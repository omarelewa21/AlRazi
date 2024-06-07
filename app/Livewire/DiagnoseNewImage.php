<?php

namespace App\Livewire;

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

    public $observations = [];

    public function mount()
    {
        $this->observations = [
            [
                'variable'  => 'Heart Rate',
                'value'     => '72 bpm'
            ],
            [
                'variable'  => 'Blood Pressure',
                'value'     => '120/80 mmHg'
            ],
            [
                'variable'  => 'Temperature',
                'value'     => '36.5 °C'
            ],
            [
                'variable'  => 'Oxygen Saturation',
                'value'     => '98%'
            ]
        ];
    }

    public function processDiagnosis()
    {
        $this->validate();

        dd($this->image);

        // Process the diagnosis here
    }
}
