<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MembershipPackage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'duration_days',
        'price',
        'description',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function members()
    {
        return $this->hasMany(Member::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}

