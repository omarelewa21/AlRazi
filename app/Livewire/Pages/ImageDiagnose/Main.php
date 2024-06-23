<?php

namespace App\Livewire\Pages\ImageDiagnose;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
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

    #[Validate('required|mimes:jpg,jpeg,png,dcm')]
    public $file;

    public $renderImages = [];
    public $observations = [];

    public function mount()
    {
        $data = $this->getSampleResponse();
        $this->setDiagnoseResponseData($data);
    }

    public function render()
    {
        return view('livewire.pages.image-diagnose.main');
    }

    public function processDiagnosis()
    {
        $this->validate();

        // $payload = $this->getImageDiagnosePayload();
        // $data = Http::post('https://api.example.com/diagnose', $payload);

        // Sample response
        $data = $this->getSampleResponse();
        $this->setDiagnoseResponseData($data);
    }

    private function getImageDiagnosePayload()
    {
        // Prepare the payload to send to the API
    }

    private function getSampleResponse()
    {
        $response = Storage::get('sample-response.json');
        return json_decode($response, true);
    }

    private function setDiagnoseResponseData($data)
    {
        $this->renderImages = collect($data['images'])->mapWithKeys(function ($image, $key) {
            return [
                Str::remove('Img', Str::headline($key)) => [
                    'url' => "data:image/png;base64,$image",
                    'visibility' => true,
                ]
            ];
        })->toArray();
        $this->observations = Arr::except($data, 'images');
    }

    public function allHidden()
    {
        return collect($this->renderImages)->every(fn ($image) => ! $image['visibility']);
    }

    public function toggleAllVisibility()
    {
        $visibility = $this->allHidden();

        $this->renderImages = collect($this->renderImages)->map(function ($image) use ($visibility) {
            $image['visibility'] = $visibility;
            return $image;
        })->toArray();
    }

    public function toggleVisibility($key)
    {
        $this->renderImages[$key]['visibility'] = ! $this->renderImages[$key]['visibility'];
    }
}
