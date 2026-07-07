<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id',
        'item_id',
        'quantity',
        'notes'
    ];

    protected $casts = [
        'template_id' => 'integer',
        'item_id' => 'integer',
        'quantity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function template()
    {
        return $this->belongsTo(Template::class);
    }

    public function item()
    {
        return $this->belongsTo(Item::class);
    }
}
