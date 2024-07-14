<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait DiagnoseTester
{
    public function mount()
    {
        $diagnoseData = $this->getTestDiagnoseData();
        $this->setDicomData($diagnoseData);
        $this->setTestSourceImages($diagnoseData);
        $this->setDiagnoseData($diagnoseData);
    }

    protected function getTestDiagnoseData()
    {
        $files = Storage::files('diagnose_test_data');
        $jsonData = [];

        foreach ($files as $index => $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'json') {
                $jsonData['image_' . $index+1] = json_decode(Storage::get($file), true);
            }
        }

        return $jsonData;
    }

    protected function setDicomData($diagnoseData)
    {
        foreach($diagnoseData as $key => $data) {
            $this->dicomData[] = [
                'image' => "data:image/png;base64,{$this->setImageSize($data['images']['source_img'])}",
                'pixel_scale' => 3.2,
            ];
        }
    }

    protected function setTestSourceImages($data)
    {
        foreach ($data as $key => $item) {
            $this->sourceImgs[$key] = [
                'url' => "data:image/png;base64,{$this->setImageSize($item['images']['source_img'])}",
                'visibility' => true,
            ];
        }
    }

    private function getDicomData()
    {
        $response = Storage::get('response.json');
        return json_decode($response, true);
    }

    private function getDiagnoseData()
    {
        $response = Storage::get('sample-response.json');
        return json_decode($response, true);
    }

    private function setReport()
    {
        $this->report = "report.pdf";
    }
}
