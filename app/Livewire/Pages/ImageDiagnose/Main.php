<?php

namespace App\Livewire\Pages\ImageDiagnose;

use App\Jobs\GenerateReport;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class Main extends Component
{
    use WithFileUploads;

    #[Validate('required|string|max:255')]
    public $name;

    #[Validate('required|string|in:male,female,other', message: 'You must choose a gender')]
    public $gender;

    #[Validate('required|date|before:today')]
    public $date_of_birth;

    #[Validate('required|email')]
    public $email;

    #[Validate('required|regex:/^\+?\d+$/')]
    public $phone;

    #[Validate('string|max:255')]
    public $referral = '';

    #[Validate('required')]
    #[Validate('mimes:dcm', message: 'Please provide a valid DICOM file')]
    public $file;

    public $diagnoseImages = [];
    public $sourceImg;
    public $observations = [];
    public $payloadObservations = [];
    public $dicomData;
    public $response;
    public $report;

    // public function mount()
    // {
    //     $data = $this->getSampleResponse();
    //     $this->setObservations($data);
    // }

    // To be removed
    private function getSampleResponse()
    {
        $response = Storage::get('sample-response.json');
        return json_decode($response, true);
    }

    public function processDiagnosis()
    {
        $this->validate();
        $this->processFile();
    }

    private function processFile()
    {
        $response = $this->getDiagnosis();
        $this->setDiagnoseData($response);
    }

    private function getDiagnosis()
    {
        $response = Http::timeout(10000)
            ->post(env('PROCESS_SERVER') . '/analyze', ['image' => $this->getSourceImageFromDicomData()]);
        return $response->json();
    }

    private function setDiagnoseData($data)
    {
        $this->setDiagnoseImages($data);
        $this->setPayloadObservations($data);
        $this->setObservations($data);
        $this->dispatchSelf('generate-report');
    }

    private function setDiagnoseImages($data)
    {
        $this->diagnoseImages = collect($data)
            ->filter(fn ($value, $key) => Str::contains($key, ['img', 'all_layers']))
            ->mapWithKeys(fn ($image, $key) => [
                trim(Str::remove('Img', Str::headline($key))) => [
                    'url' => "data:image/png;base64,{$this->setImageSize($image)}",
                    'visibility' => true,
                ]
            ])
            ->except('Source')
            ->toArray();
    }

    private function setPayloadObservations($data)
    {
        $this->payloadObservations = collect($data)->reject(fn ($value, $key) => Str::contains($key, ['img', 'all_layers']))
            ->toArray();
    }

    private function setObservations($data)
    {
        $this->observations = collect($data)->reject(fn ($value, $key) => Str::contains($key, ['img', 'all_layers']))
            ->mapWithKeys(function ($value, $key) {
                $return = [];
                foreach ($value as $k => $v) {
                    if(is_array($v)) {
                        $return[Str::headline($k)] = $this->formatObsArray($v);
                    } else {
                        $return[Str::headline($k)] = $v;
                    }
                }
                return [Str::headline($key) => $return];
            });
    }

    public function allHidden()
    {
        return collect($this->diagnoseImages)->every(fn ($image) => ! $image['visibility']);
    }

    public function toggleAllVisibility()
    {
        $visibility = $this->allHidden();

        $this->diagnoseImages = collect($this->diagnoseImages)->map(function ($image) use ($visibility) {
            $image['visibility'] = $visibility;
            return $image;
        })->toArray();
    }

    public function toggleVisibility($key)
    {
        $this->diagnoseImages[$key]['visibility'] = ! $this->diagnoseImages[$key]['visibility'];
    }

    private function parseDicomFile()
    {
        $this->file->storeAs('dicom', $this->file->hashName(), 'shared');
        $response = Http::acceptJson()->get(sprintf("%s/%s", config('app.dicom_server'), $this->file->hashName()));
        Storage::disk('shared')->delete("dicom/{$this->file->hashName()}");
        return $response->json();
    }

    private function formatObsArray($array)
    {
        return collect($array)->map(function ($value, $key) {
            return "<span>$key = $value</span><br>";
        })->implode('');
    }

    private function getSourceImageFromDicomData()
    {
        return collect($this->dicomData)
            ->firstOrFail(fn($value, $key) => Str::contains($key, 'image'));
    }

    private function setSourceImage()
    {
        $this->sourceImg = [
            'url' => "data:image/png;base64,{$this->setImageSize($this->getSourceImageFromDicomData())}",
            'visibility' => true,
        ];
    }

    private function setImageSize($base64Image)
    {
        $image = imagecreatefromstring(base64_decode($base64Image));
        $width = imagesx($image);
        $height = imagesy($image);
        $aspectRatio = $height / $width;
        $newHeight = 600;
        $newWidth = $newHeight / $aspectRatio;
        $newImage = imagecreatetruecolor($newWidth, $newHeight);
        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        ob_start();
        imagepng($newImage);
        $image = ob_get_contents();
        ob_end_clean();
        $this->sourceImg['width'] = $newWidth;
        $this->sourceImg['height'] = $newHeight;
        return base64_encode($image);
    }

    public function updatedFile()
    {
        $this->validateOnly('file');
        $this->dicomData = match ($this->file->getClientOriginalExtension()) {
            'dcm' => $this->parseDicomFile(),
            default => $this->parseDicomFile(),
        };
        $this->setSourceImage();
    }

    #[On('generate-report')]
    public function generateReport()
    {
        GenerateReport::dispatch($this->file->hashName(), $this->payloadObservations);
        $this->report = "reports/{$this->file->hashName()}.pdf";
    }

    public function showReport()
    {
        $maxExecTimesExceeded = 15;
        $counter = 0;

        while($counter < $maxExecTimesExceeded && !Storage::disk('public')->exists($this->report)) {
            sleep(2);
        }

        if(!Storage::disk('public')->exists($this->report)) {
            dd('Report not found');
        }

        return response()->file(Storage::disk('public')->path($this->report),
            ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="report.pdf"']);
    }
}
