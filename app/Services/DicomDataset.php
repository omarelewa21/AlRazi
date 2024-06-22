<?php

namespace App\Services;

class DicomDataset
{
    protected $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function __get($name)
    {
        $tag = $this->attributeToTag($name);
        if (isset($this->data[$tag])) {
            return $this->data[$tag]['value'];
        }
        return null;
    }

    protected function attributeToTag($name)
    {
        // Mapping attribute names to DICOM tags
        $mapping = [
            'PatientName' => '02000000',
            'PatientID' => '00100020',
            'PatientBirthDate' => '00100030',
            // Add more attribute mappings here
        ];

        return $mapping[$name] ?? null;
    }
}
