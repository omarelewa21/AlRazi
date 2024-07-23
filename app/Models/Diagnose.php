<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Diagnose extends Model
{
    use HasFactory;

    protected $fillable = [
        'dcm_files',
        'source_imgs',
        'diagnose_imgs',
        'observations',
        'report',
        'status',
    ];

    protected $casts = [
        'dcm_files' => 'array',
        'source_imgs' => 'array',
        'diagnose_imgs' => 'array',
        'observations' => 'array',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function getDcmFile()
    {
        return Storage::disk('shared')->get($this->dcm_file);
    }

    public function getReportPath()
    {
        return Storage::disk('public')->path($this->report);
    }
}
