<?php

namespace App\Livewire\Pages\ImageDiagnose;

use App\Models\Diagnose;
use LivewireUI\Modal\ModalComponent;

class AddSample extends ModalComponent
{
    public $diagnoses;

    public $diagnosesToAdd = [];

    public function mount()
    {
        $this->diagnoses = Diagnose::samples()->select('id', 'source_imgs', 'observations')->get()
            ->map(fn (Diagnose $diagnose) => $this->formatDiagnose($diagnose));
    }

    public function render()
    {
        return view('livewire.pages.image-diagnose.add-sample');
    }

    public function singleCases()
    {
        return $this->diagnoses->filter(fn ($diagnose) => count($diagnose) === 1)->values();
    }

    public function compoundCases()
    {
        return $this->diagnoses->filter(fn ($diagnose) => count($diagnose) > 1)->values();
    }

    public function formatDiagnose(Diagnose $diagnose)
    {
        $data = [];
        foreach ($diagnose->source_imgs as $key => $sourceImg) {
            $data[$key]['id'] = $diagnose->id;
            $data[$key]['url'] = $sourceImg['url'];
            $data[$key]['view'] = str()->headline($diagnose->observations[$key]['observations']['view']);
        }
        return $data;
    }

    public function addSamplesToWorkList()
    {
        $diagnoses = Diagnose::samples()->whereIn('id', $this->diagnosesToAdd)->with('patient')->get();
        foreach ($diagnoses as $diagnose) {
            $newPatient = $diagnose->patient->replicate();
            $newPatient->user_id = auth()->id();
            $newPatient->save();
            $newDiagnose = $diagnose->replicate();
            $newDiagnose->patient_id = $newPatient->id;
            $newDiagnose->is_sample = false;
            $newDiagnose->save();
        }
        $this->closeModal();
        $this->dispatch('refresh-work-list');
    }
}
