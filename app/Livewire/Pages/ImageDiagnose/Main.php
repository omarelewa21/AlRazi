<?php

namespace App\Livewire\Pages\ImageDiagnose;

use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
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

    public $images = [];
    public $sourceImg = [];
    public $observations = [];

    public function mount()
    {
        $data = $this->getSampleResponse();
        $this->setDiagnoseResponseData($data);
        $this->observations = $this->getObservations($data);
    }

    public function render()
    {
        return view('livewire.pages.image-diagnose.main');
    }

    public function processDiagnosis()
    {
        $this->validate();
        $this->processFile();

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
        $this->images = collect($data['images'])
            ->mapWithKeys(fn ($image, $key) => [
                trim(Str::remove('Img', Str::headline($key))) => [
                    'url' => "data:image/png;base64,$image",
                    'visibility' => true,
                ]
            ]);
        $this->sourceImg = $this->images->get('Source');
        $this->images = $this->images->except('Source')->toArray();
        $this->observations = Arr::except($data, 'images');
    }

    public function allHidden()
    {
        return collect($this->images)->every(fn ($image) => ! $image['visibility']);
    }

    public function toggleAllVisibility()
    {
        $visibility = $this->allHidden();

        $this->images = collect($this->images)->map(function ($image) use ($visibility) {
            $image['visibility'] = $visibility;
            return $image;
        })->toArray();
    }

    public function toggleVisibility($key)
    {
        $this->images[$key]['visibility'] = ! $this->images[$key]['visibility'];
    }

    private function processFile()
    {
        $fileExtension = $this->file->getClientOriginalExtension();
        match ($fileExtension) {
            'dcm' => $this->processDicomFile(),
            default => $this->processImageFile(),
        };
    }

    private function processDicomFile()
    {
        $this->file->storeAs('dicom', $this->file->hashName(), 'shared');
        $response = Http::get(sprintf("%s/%s", config('app.dicom_server'), $this->file->hashName()));
        Storage::disk('shared')->delete("dicom/{$this->file->hashName()}");
    }

    private function processImageFile()
    {
    }

    private function getObservations($data)
    {
        return collect($data)->except('images')
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

    private function formatObsArray($array)
    {
        return collect($array)->map(function ($value, $key) {
            return "<span>$key => $value</span><br>";
        })->implode('');
    }
}
