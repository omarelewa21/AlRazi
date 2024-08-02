<?php

namespace App\Livewire\Pages\ImageDiagnose;

use App\Jobs\QuickUpload as QuickUploadJob;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Livewire\Features\SupportFileUploads\WithFileUploads;
use LivewireUI\Modal\ModalComponent;

class QuickUpload extends ModalComponent
{
    use WithFileUploads;

    #[Validate('required|array|min:1|max:10')]
    #[Validate(['files.*' => 'file|mimes:dcm'])]
    public $files = [];

    public function render()
    {
        return view('livewire.pages.image-diagnose.quick-upload');
    }

    public function process()
    {
        $this->validate();

        QuickUploadJob::dispatch($this->storeFiles(), auth()->user());

        $this->dispatch('show-info',
            type: 'success',
            title: 'Files uploaded successfully',
            description: 'It may take a few minutes to process the files, once done you will receive an email notification.',
        );
    }

    public function updatedFiles()
    {
        $this->validateOnly('files');
    }

    #[On('close-modal')]
    public function _closeModal()
    {
        $this->closeModal();
    }

    protected function storeFiles(): array
    {
        $files = [];

        foreach ($this->files as $file) {
            $file->storeAs('dicom', $file->hashName(), 'shared');
            $files[] = $file->hashName();
        }

        return $files;
    }
}
