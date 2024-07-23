<?php

namespace App\Livewire\Pages\ImageDiagnose;

use App\Models\Diagnose;

class Show extends Create
{
    public function mount(Diagnose $diagnose)
    {
        $this->diagnoseModel = $diagnose;
        $this->setPatientInfo();
        $this->sourceImgs = $diagnose->source_imgs;
        $this->observations = $diagnose->observations;
        $this->diagnoseImages = $diagnose->diagnose_imgs;
        $this->report = $diagnose->report;
    }

    protected function setPatientInfo()
    {
        $this->name = $this->diagnoseModel->patient->name;
        $this->gender = $this->diagnoseModel->patient->gender;
        $this->date_of_birth = $this->diagnoseModel->patient->date_of_birth->format('Y-m-d');
        $this->email = $this->diagnoseModel->patient->email;
        $this->phone = $this->diagnoseModel->patient->phone;
        $this->referral = $this->diagnoseModel->patient->referral;
    }
}
