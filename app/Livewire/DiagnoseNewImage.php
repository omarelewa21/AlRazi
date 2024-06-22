<?php

namespace App\Livewire;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class DiagnoseNewImage extends Component
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

    #[Validate('required|mimes:jpg,jpeg,png,dcm')]
    public $file;

    public $responseImages = [];
    public $imageIds = [];
    public $observations = [];

    public function processDiagnosis()
    {
        $this->validate();

        $this->processFile();
        // $payload = $this->getImageDiagnosePayload();
        // $response = Http::post('https://api.example.com/diagnose', $payload);

        // Sample response
        // file_get_contents("storage/cervical.json")
        $response = Storage::get('cervical.json');
        $response = json_decode($response, true);

        $this->setDiagnoseResponseData($response);

        $this->dispatch('cornerstone-images-render', images: $this->imageIds);
    }

    private function getImageDiagnosePayload()
    {
        // Prepare the payload to send to the API
    }

    private function setDiagnoseResponseData($response)
    {
        $this->responseImages = $response['images'];
        $this->imageIds = collect($response['images'])->flatten()->map(function ($base64Image, $index) {
            $randomName = Str::random(10);
            Storage::disk('public')->put("images/$randomName.png", base64_decode($base64Image));
            return asset("storage/images/$randomName.png");
        });
        $this->observations = Arr::except($response, 'images');
    }

    private function processFile()
    {
        $fileExtension = $this->file->getClientOriginalExtension();
        return match ($fileExtension) {
            'dcm' => $this->processDicomFile(),
            default => $this->processImageFile(),
        };
    }

    private function processDicomFile()
    {
        $this->file->storeAs('dicom', $this->file->hashName(), 'public');
        // $dicomParser = new DicomParser(public_path('storage/dicom/' . $this->file->hashName()));
        // $dicomDataset = $dicomParser->parse();
        // dd($dicomDataset->PatientName);


    }
}
