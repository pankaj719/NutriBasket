<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    protected $fillable = ['user_id', 'client_id', 'name', 'status', 'end_date'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function items()
    {
        return $this->hasMany(\App\Models\ContractItem::class);
    }

    public function client()
    {
        return $this->belongsTo(\App\Models\B2BClient::class, 'client_id');
    }
}