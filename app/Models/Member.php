<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Member extends Model
{
    use HasFactory;

    protected $fillable = [
        'full_name',
        'phone',
        'email',
        'gender',
        'membership_package_id',
        'membership_type',
        'membership_start',
        'membership_end',
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
    public function membershipPackage()
    {
        return $this->belongsTo(MembershipPackage::class);
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
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

    public function scopeExpiringSoon($query, $days = 7)
    {
        return $query->where('is_active', true)
                     ->whereBetween('membership_end', [now(), now()->addDays($days)]);
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
        return $this->membership_end->isPast();
    }

    public function daysUntilExpiration()
    {
        if (!$this->membership_end) {
            return null;
        }
        
        $days = now()->diffInDays($this->membership_end, false);
        return $days > 0 ? (int) $days : 0;
    }

    public function getStatusAttribute()
    {
        if (!$this->is_active) {
            return 'inactive';
        }
        
        if ($this->isExpired()) {
            return 'expired';
        }
        
        $daysUntil = $this->daysUntilExpiration();
        if ($daysUntil !== null && $daysUntil <= 7) {
            return 'expiring_soon';
        }
        
        return 'active';
    }

    public function getDaysUntilExpirationAttribute()
    {
        return $this->daysUntilExpiration();
    }
}
