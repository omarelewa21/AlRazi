<?php

namespace App\Traits;

use Illuminate\Support\Facades\Storage;

trait DiagnoseTester
{
    public function mount()
    {
        $this->dicomData = $this->getDicomData();
        $data = $this->getDiagnoseData();
        $this->setSourceImage();
        $this->setDiagnoseImages($data);
        $this->setObservations($data);
        $this->setReport();
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
