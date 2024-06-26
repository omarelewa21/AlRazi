<?php

namespace App\Livewire\Pages\ImageDiagnose;

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

    #[Validate('required')]
    #[Validate('mimes:dcm', message: 'Please provide a valid DICOM file')]
    public $file;

    public $images = [];
    public $sourceImg;
    public $observations = [];

    public function mount()
    {
        $data = $this->getSampleResponse();
        // $this->setDiagnoseResponseData($data);
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
        // $data = $this->getSampleResponse();
        // $this->setDiagnoseResponseData($data);
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
        $this->images = collect($data)
            ->filter(fn ($value, $key) => Str::contains($key, ['img', 'all_layers']))
            ->mapWithKeys(fn ($image, $key) => [
                trim(Str::remove('Img', Str::headline($key))) => [
                    'url' => "data:image/png;base64,$image",
                    'visibility' => true,
                ]
            ])
            ->except('Source')
            ->toArray();
        // $this->observations = $this->getObservations($data);
        // $this->sourceImg = [
        //     'url' => "data:image/png;base64,{$this->setImageSize($data['source_img'])}",
        //     'visibility' => true,
        // ];
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
        $payload = match ($fileExtension) {
            'dcm' => $this->processDicomFile(),
            default => $this->processImageFile(),
        };
        $this->setSourceImage($payload);
        $response = $this->getResponse($payload);
        $this->setDiagnoseResponseData($response);

    }

    private function processDicomFile()
    {
        $this->file->storeAs('dicom', $this->file->hashName(), 'shared');
        $response = Http::acceptJson()->get(sprintf("%s/%s", config('app.dicom_server'), $this->file->hashName()));
        Storage::disk('shared')->delete("dicom/{$this->file->hashName()}");
        return $response->json();
    }

    private function processImageFile()
    {
    }

    private function getObservations($data)
    {
        return collect($data)->reject(fn ($value, $key) => Str::contains($key, ['img', 'all_layers']))
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
            return "<span>$key = $value</span><br>";
        })->implode('');
    }

    private function getResponse($payload)
    {
        $response = Http::timeout(10000)->post(env('PROCESS_SERVER'), ['image' => $this->getImageFromPayload($payload)]);
        return $response->json();
    }

    private function getImageFromPayload($payload)
    {
        $image = collect($payload)
            ->firstOrFail(fn($value, $key) => Str::contains($key, 'image'));
        return $this->setImageSize($image);
    }

    private function setSourceImage($payload)
    {
        $this->sourceImg = [
            'url' => "data:image/png;base64,{$this->getImageFromPayload($payload)}",
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
        return base64_encode($image);
    }
}
