<?php

namespace App\Jobs;

use App\Mail\DiagnoseCompleted;
use App\Models\Diagnose;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class QuickUpload implements ShouldQueue
{
    use Queueable;

    public $timeout = 10000;

    protected array $diagnoseImages = [];
    protected array $observations = [];
    protected string $report = '';

    /**
     * Create a new job instance.
     */
    public function __construct(protected array $files, protected User $user)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->parseDicomFiles();
    }

    protected function parseDicomFiles(): void
    {
        $this->parseFiles($this->files);
    }

    protected function parseFiles(array $files): void
    {
        $jsonResponse = $this->getParseResponse($files);
        foreach($jsonResponse as $patientInfo) {
            $diagnoses = $this->getDiagnoses($patientInfo);
            foreach($diagnoses as $key => $data) {
                $diagnoses[$key] = json_decode($data, true);
            }
            $this->setDiagnoseData($diagnoses, $patientInfo);
        }

        Mail::to($this->user->email)->send(new DiagnoseCompleted());
    }

    protected function getParseResponse(array $files): array
    {
        $response = Http::acceptJson()->post(sprintf("%s/process_files", config('app.dicom_parse_server')), $files);
        return $response->json();
    }

    protected function storePatient(array $patientInfo): Patient
    {
        return Patient::create([
            'user_id' => $this->user->id,
            'name' => $patientInfo['patient_name'],
            'gender' => $patientInfo['sex'],
            'date_of_birth' => $this->getPatientBirthDate($patientInfo['birth_date'])
        ]);
    }

    protected function getPatientBirthDate(string $birthDate): string
    {
        return sprintf('%s-%s-%s', substr($birthDate, 0, 4), substr($birthDate, 4, 2), substr($birthDate, 6, 2));
    }

    protected function storeDiagnose(Patient $patient, array $patientInfo): void
    {
        $patient->diagnoses()->create([
            'dcm_files' => $this->getFileHashes($patientInfo['worklist_ids']),
            'source_imgs' => $this->getSourceImages($patientInfo),
            'diagnose_imgs' => $this->diagnoseImages,
            'observations' => $this->observations,
            'report' => $this->report,
        ]);
    }

    protected function getFileHashes(array $workListIds): array
    {
        return collect($this->files)->filter(
            fn(string $fileHash, int $index) => in_array($index, $workListIds)
        )->toArray();
    }

    protected function getSourceImages(array $patientInfo): array
    {
        $images = collect($patientInfo)->filter(
            fn($value, string $key) => Str::contains($key, 'image_') && !is_null($value)
        );

        $pixelArrays = collect($patientInfo)->filter(
            fn($value, string $key) => Str::contains($key, 'pixel_scale') && !is_null($value)
        );

        return $images->mapWithKeys(
            fn($image, string $key) => [$key => [
                'url' => "data:image/png;base64,{$this->setImageSize($image)}",
                'pixel_scale' => $pixelArrays->get("pixel_scale_" . Str::after($key, 'image_')),
            ]]
        )->toArray();
    }

    protected function getDiagnoses(array $patientInfo)
    {
        $response = Http::timeout(10000)
            ->post(config("app.process_server") . '/analyze', $this->getPayloadForDiagnosis($patientInfo));
        return $response->json();
    }

    protected function getPayloadForDiagnosis(array $patientInfo): array
    {
        return collect($patientInfo)->filter(
            fn($value, string $key) => Str::contains($key, 'image_') && !is_null($value)
        )->mapWithKeys(
            fn($image, string $key) => [$key => [
                "image" => $image,
                "pixel_scale" => $patientInfo["pixel_scale_" . Str::after($key, 'image_')],
            ]]
        )->toArray();
    }

    private function setDiagnoseData($data, $patientInfo)
    {
        $this->setDiagnoseImages($data);
        $this->setObservations($data);
        $this->saveDiagnosis($data, $patientInfo);
        $this->generateReport();
    }

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

    private function setObservations($diagnoseData)
    {
        foreach($diagnoseData as $key => $data) {
            $this->observations[$key]['observations'] = $data['observations'];
        }
    }

    private function generateReport()
    {
        $randomString = Str::random(10);
        $fileName = sprintf("%s.pdf", $randomString);
        GenerateReport::dispatch($fileName, Diagnose::latest()->first());
        $this->report = "reports/{$fileName}";
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

    private function saveDiagnosis($data, $patientInfo)
    {
        $patient = $this->storePatient($patientInfo);
        $this->storeDiagnose($patient, $patientInfo);
    }
}
