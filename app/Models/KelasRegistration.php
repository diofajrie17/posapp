<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KelasRegistration extends Model
{
    use HasFactory;

    protected $table = 'kelas_registrations';

    protected $fillable = [
        'kelas_id',
        'member_id',
        'registration_date',
        'start_date',
        'end_date',
        'status',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'registration_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function member()
    {
        return $this->belongsTo(KelasMember::class, 'member_id');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}


