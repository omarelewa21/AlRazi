<?php

namespace App\Livewire\Pages\ImageDiagnose;

use App\Jobs\GenerateReport;
use App\Traits\DiagnoseTester;
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
    use DiagnoseTester;

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
    #[Validate(['files.*' => 'file|mimes:dcm'])]
    public $files;

    public $diagnoseImages = [];
    public $sourceImgs = [];
    public $observations = [];
    public $payloadObservations = [];
    public $dicomData = [];
    public $response;
    public $report;

    // First Step
    public function updatedFiles()
    {
        $this->validateOnly('files');
        $this->resetDiganoseData();
        foreach ($this->files as $file) {
            $this->dicomData[] = $this->parseDicomFile($file);
        }
        $this->setSourceImages();
    }

    // Second Step
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

    // Third Step
    private function getDiagnosis()
    {
        $response = Http::timeout(10000)
            ->post(config("app.process_server") . '/analyze', $this->getPayloadForDiagnosis());
        return $response->json();
    }

    private function getPayloadForDiagnosis()
    {
        return collect($this->dicomData)->mapWithKeys(
            fn ($data, $index) => ["image_" . $index+1 => [
                "image" => $this->getSourceImageFromDicomData($data),
                "pixel_scale" => $data['pixel_scale'],
            ]]
        )
        ->toArray();
    }

    // Fourth Step
    private function setDiagnoseData($data)
    {
        $this->setDiagnoseImages($data);
        $this->setPayloadObservations($data);
        $this->setObservations($data);
        $this->dispatch('generate-report');
    }

    // Fifth Step
    private function setDiagnoseImages($diagnoseData)
    {
        foreach($diagnoseData as $key => $data) {
            $this->diagnoseImages[$key] = collect($data)
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
    }

    // Sixth Step
    private function setPayloadObservations($diagnoseData)
    {
        foreach($diagnoseData as $key => $data) {
            $this->payloadObservations[$key] = collect($data)->reject(fn ($value, $key) => Str::contains($key, ['img', 'all_layers']))
                ->toArray();
        }
    }

    // Seventh Step
    private function setObservations($data)
    {
        foreach($data as $key => $item) {
            $this->observations[Str::headline($key)] = collect($item)->reject(fn ($value, $key) => Str::contains($key, ['img', 'all_layers']))
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

    private function parseDicomFile($file)
    {
        $file->storeAs('dicom', $file->hashName(), 'shared');
        $response = Http::acceptJson()->get(sprintf("%s/%s", config('app.dicom_parse_server'), $file->hashName()));
        Storage::disk('shared')->delete("dicom/{$file->hashName()}");
        return $response->json();
    }

    private function formatObsArray($array)
    {
        return collect($array)->map(function ($value, $key) {
            return "<span>$key = $value</span><br>";
        })->implode('');
    }

    private function setSourceImages()
    {
        foreach($this->dicomData as $index => $data) {
            $this->sourceImgs['image_' . $index+1] = [
                'url' => "data:image/png;base64,{$this->setImageSize($this->getSourceImageFromDicomData($data))}",
                'visibility' => true,
            ];
        }
    }

    private function getSourceImageFromDicomData($data)
    {
        return collect($data)->firstOrFail(fn($value, $key) => Str::contains($key, 'image'));
    }

    private function setImageSize($base64Image)
    {
        $image = imagecreatefromstring(base64_decode($base64Image));
        $width = imagesx($image);
        $height = imagesy($image);
        $aspectRatio = $height / $width;
        $newHeight = 680;
        $newWidth = $newHeight / $aspectRatio;
        $newImage = imagecreatetruecolor($newWidth, $newHeight);

        // Preserve transparency
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 0, 0, 0, 127);
        imagefill($newImage, 0, 0, $transparent);
        imagecolortransparent($newImage, $transparent);

        imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
        ob_start();
        imagepng($newImage);
        $imageData = ob_get_contents();
        ob_end_clean();
        return base64_encode($imageData);
    }

    #[On('generate-report')]
    public function generateReport()
    {
        $randomString = Str::random(10);
        $fileName = sprintf("%s.pdf", $randomString);
        GenerateReport::dispatch($fileName, $this->payloadObservations);
        $this->report = "reports/{$fileName}";
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

    public function resetDiganoseData()
    {
        $this->dicomData = [];
        $this->sourceImgs = [];
        if($this->report) $this->deleteReport();
    }

    public function deleteReport()
    {
        if(Storage::disk('public')->exists($this->report)) {
            Storage::disk('public')->delete($this->report);
        }
    }
}
