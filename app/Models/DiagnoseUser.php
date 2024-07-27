<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DiagnoseUser extends Model
{
    use HasFactory;

    protected $table = 'diagnose_user';

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($model) {
            $model->referred_by = auth()->id();
        });
    }

    protected $fillable = [
        'diagnose_id',
        'user_id',
        'priority',
        'referred_by',
    ];

    public function diagnose()
    {
        return $this->belongsTo(Diagnose::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function referredBy()
    {
        return $this->belongsTo(User::class, 'referred_by');
    }
}
