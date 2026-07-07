<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryWastage extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'quantity',
        'price_per_unit',
        'total_value',
        'category_id',
        'category_name',
        'reason',
        'created_by'
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'price_per_unit' => 'decimal:2',
        'total_value' => 'decimal:2',
    ];

    public function item()
    {
        return $this->belongsTo(Item::class);
    }

    public function category()
    {
        return $this->belongsTo(WastageCategory::class, 'category_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }
} 