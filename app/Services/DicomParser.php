<?php

namespace App\Services;

class DicomParser
{
    public function __construct(protected string $filePath)
    {
    }

    public function parse()
    {
        $dicomData = $this->readDicomFile($this->filePath);
        $parsedData = $this->parseDicomData($dicomData);
        return new DicomDataset($parsedData);
    }

    protected function readDicomFile($filePath)
    {
        $fileContents = file_get_contents($filePath);
        if ($fileContents === false) {
            throw new \Exception("Failed to read file: " . $filePath);
        }
        return $fileContents;
    }

    protected function parseDicomData($data)
    {
        $parsedData = [];

        $header = substr($data, 0, 128); // DICOM preamble
        $dicomPrefix = substr($data, 128, 4); // DICOM prefix 'DICM'
        $parsedData['header'] = bin2hex($header);
        $parsedData['prefix'] = $dicomPrefix;

        $offset = 132; // Start after the DICOM prefix

        while ($offset < strlen($data)) {
            $tag = bin2hex(substr($data, $offset, 4));
            $offset += 4;
            $vr = substr($data, $offset, 2);
            $offset += 2;

            if (in_array($vr, ['OB', 'OW', 'OF', 'SQ', 'UT', 'UN'])) {
                $offset += 2; // Skip reserved bytes
                $length = unpack('V', substr($data, $offset, 4))[1];
                $offset += 4;
            } else {
                $length = unpack('v', substr($data, $offset, 2))[1];
                $offset += 2;
            }

            $value = substr($data, $offset, $length);
            $offset += $length;

            $parsedData[$tag] = [
                'vr' => $vr,
                'length' => $length,
                'value' => $this->decodeValue($vr, $value),
            ];
        }

        return $parsedData;
    }

    protected function decodeValue($vr, $value)
    {
        switch ($vr) {
            case 'PN':
                return trim($value);
            case 'DA':
                return date('Y-m-d', strtotime($value));
            // Add more VR cases as needed
            default:
                return $value;
        }
    }
}
