<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KelasExpense extends Model
{
    use HasFactory;

    protected $table = 'kelas_expenses';

    protected $fillable = [
        'kelas_id',
        'expense_date',
        'category',
        'description',
        'amount',
        'created_by',
    ];

    protected $casts = [
        'expense_date' => 'date',
        'amount' => 'decimal:2',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}


