<?php

namespace App\Livewire\Pages\ImageDiagnose;

use App\Jobs\GenerateReport;
use App\Models\Diagnose;
use App\Models\Patient;
use App\Traits\DiagnoseTester;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\Features\SupportFileUploads\WithFileUploads;

class Create extends Component
{
    use WithFileUploads;
    // use DiagnoseTester;

    public function render()
    {
        return view('livewire.pages.image-diagnose.create')->layout('layouts.diagnose');
    }

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

    public $diagnoseModel;
    public $diagnoseImages = [];
    public $sourceImgs = [];
    public $observations = [];
    public $dicomData = [];
    public $report;
    public bool $observationsChanged = false;

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
        $this->saveDiagnosis();
    }

    private function processFile()
    {
        $response = $this->getDiagnosis();
        foreach($response as $key => $data) {
            $response[$key] = json_decode($data, true);
        }
        $this->setDiagnoseData($response);
    }

    private function saveDiagnosis()
    {
        $patient = Patient::create([
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'gender' => $this->gender,
            'date_of_birth' => $this->date_of_birth,
            'referral' => $this->referral,
        ]);

        $this->diagnoseModel = Diagnose::create([
            'patient_id' => $patient->id,
            'dcm_files' => collect($this->files)->map(fn($file) => $file->hashName())->toArray(),
            'source_imgs' => $this->sourceImgs,
            'diagnose_imgs' => $this->diagnoseImages,
            'observations' => $this->observations,
        ]);
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
        $this->setObservations($data);
        $this->dispatch('generate-report');
    }

    // Fifth Step
    private function setDiagnoseImages($diagnoseData)
    {
        foreach($diagnoseData as $key => $data) {
            $this->diagnoseImages[$key] = collect($data['images'])
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
    private function setObservations($diagnoseData)
    {
        foreach($diagnoseData as $key => $data) {
            $this->observations[$key]['observations'] = $data['observations'];
        }
    }

    private function parseDicomFile($file)
    {
        $file->storeAs('dicom', $file->hashName(), 'shared');
        $response = Http::acceptJson()->post(sprintf("%s/extract_image_from_dicom", config('app.dicom_parse_server')), ['dicom_file' => $file->hashName()]);
        return $response->json();
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
        $newHeight = 600;
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
        (new GenerateReport($fileName, $this->diagnoseModel))->handle();
        // GenerateReport::dispatch($fileName, $this->diagnoseModel);
        $this->report = "reports/{$fileName}";
    }

    public function showReport()
    {
       if($this->observationsChanged) {
            $this->diagnoseModel->update(['observations' => $this->observations]);
            $this->generateReport();
            $this->observationsChanged = false;
        }

        // To be removed
        if(!$this->report) {
            $this->generateReport();
        }

        return response()->file(Storage::disk('public')->path($this->report),
            ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="report.pdf"']);



        $maxExecTimesExceeded = 1;
        $counter = 0;

        while($counter < $maxExecTimesExceeded && !Storage::disk('public')->exists($this->report)) {
            sleep(2);
            $counter++;
        }

        if(!Storage::disk('public')->exists($this->report)) {
            return;
        }

        return response()->file(Storage::disk('public')->path($this->report),
            ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="report.pdf"']);
    }

    public function resetDiganoseData()
    {
        $this->dicomData = [];
        $this->sourceImgs = [];
        $this->diagnoseImages = [];
        $this->observations = [];
        $this->report = null;
        if($this->report) $this->deleteReport();
    }

    public function deleteReport()
    {
        if(Storage::disk('public')->exists($this->report)) {
            Storage::disk('public')->delete($this->report);
        }
    }
}
