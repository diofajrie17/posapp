<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    protected $fillable = ['member_id','channel','message','send_date','sent_at','status','meta'];
    protected $casts = [
        'send_date' => 'date',
        'sent_at'   => 'datetime',
    ];
    public function member(){ return $this->belongsTo(Member::class); }
}
