<?php

namespace App\Livewire;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
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

    #[Validate('required|image|max:1024|mimes:jpg,jpeg,png')]
    public $image;

    public $responseImages = [];
    public $imageSources = [];
    public $observations = [];

    public function processDiagnosis()
    {
        $this->validate();

        // $payload = $this->getImageDiagnosePayload();
        // $response = Http::post('https://api.example.com/diagnose', $payload);

        // Sample response
        $response = file_get_contents("storage/cervical.json");
        $response = json_decode($response, true);

        $this->setDiagnoseResponseData($response);

        $this->dispatch('display-images', $this->imageSources);
    }

    private function getImageDiagnosePayload()
    {
        // Prepare the payload to send to the API
    }

    private function setDiagnoseResponseData($response)
    {
        $this->responseImages = $response['images'];
        $this->imageSources = collect($response['images'])->map(function ($image) {
            return "data:image/png;base64,$image";
        })->toArray();
        $this->observations = Arr::except($response, 'images');
    }
}
