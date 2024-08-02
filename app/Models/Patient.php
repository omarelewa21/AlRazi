<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
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

    protected $appends = ['age'];

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($model) {
            if($model->user_id === null) {
                $model->user_id = auth()->id();
            };
        });
    }

    public function diagnoses()
    {
        return $this->hasMany(Diagnose::class);
    }

    protected function age(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => (int) round(Carbon::parse($attributes['date_of_birth'])->diffInYears(now())),
        );
    }

    protected function gender(): Attribute
    {
        return Attribute::make(
            set: fn($value) => strtolower($value) === 'f' ? 'female' : 'male',
        );
    }
}
