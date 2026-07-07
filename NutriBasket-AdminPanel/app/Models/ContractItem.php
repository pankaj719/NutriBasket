<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ContractItem extends Model
{
    protected $fillable = ['contract_id', 'item_id', 'price'];
    
    public $timestamps = true;

    public function contract()
    {
        return $this->belongsTo(Contract::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}