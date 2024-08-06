<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use App\Models\Scopes\RejectSamples;
use Illuminate\Database\Eloquent\Builder;

#[ScopedBy([RejectSamples::class])]
class Diagnose extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'dcm_files',
        'source_imgs',
        'diagnose_imgs',
        'observations',
        'report',
        'status',
        'is_sample',
    ];

    protected $casts = [
        'dcm_files' => 'array',
        'source_imgs' => 'array',
        'diagnose_imgs' => 'array',
        'observations' => 'array',
    ];

    public function scopeSamples(Builder $query): void
    {
        $query->withoutGlobalScope(RejectSamples::class)->where('is_sample', true);
    }

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

    public function users()
    {
        return $this->belongsToMany(User::class)->withPivot('priority', 'referred_by')
            ->wherePivot('user_id', auth()->id());
    }

    public function referrals()
    {
        return $this->hasManyThrough(User::class, DiagnoseUser::class, 'diagnose_id', 'id', 'id', 'referred_by')->where('user_id', auth()->id());
    }

    public function DiganoseUser()
    {
        return $this->hasOne(DiagnoseUser::class)->where('user_id', auth()->id());
    }
}
