<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'gender',
        'referral',
        'date_of_birth',
    ];

    protected $casts = [
        'date_of_birth' => 'date:Y-m-d',
        'created_at' => 'datetime:Y-m-d H:i A',
    ];

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($model) {
            $model->user_id = auth()->id();
        });
    }

    public function diagnoses()
    {
        return $this->hasMany(Diagnose::class);
    }
}
