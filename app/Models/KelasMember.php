<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KelasMember extends Model
{
    use HasFactory;

    protected $table = 'kelas_members';

    protected $fillable = [
        'kelas_id',
        'full_name',
        'phone',
        'membership_type',
        'membership_start',
        'membership_end',
        'amount',
        'payment_method',
        'is_active',
        'notes',
    ];

    protected $casts = [
        'membership_start' => 'date',
        'membership_end' => 'date',
        'is_active' => 'boolean',
    ];

    protected $appends = ['status', 'days_until_expiration'];

    // Relationships
    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function registrations()
    {
        return $this->hasMany(KelasRegistration::class, 'member_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
                     ->where(function($q) {
                         $q->whereNull('membership_end')
                           ->orWhere('membership_end', '>=', now());
                     });
    }

    public function scopeExpired($query)
    {
        return $query->where('membership_end', '<', now());
    }

    public function scopeMonthly($query)
    {
        return $query->where('membership_type', 'monthly');
    }

    public function scopeDaily($query)
    {
        return $query->where('membership_type', 'daily');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function($q) use ($search) {
            $q->where('full_name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    // Helper Methods
    public function isExpired()
    {
        if (!$this->membership_end) {
            return false;
        }
        return $this->membership_end < now();
    }

    public function getStatusAttribute()
    {
        if ($this->membership_type === 'daily') {
            return 'active';
        }
        
        if ($this->isExpired()) {
            return 'expired';
        }
        
        return 'active';
    }

    public function getDaysUntilExpirationAttribute()
    {
        if (!$this->membership_end) {
            return null;
        }
        
        if ($this->isExpired()) {
            return 0;
        }
        
        return now()->diffInDays($this->membership_end);
    }
}

