<?php
// app/Models/OrderChangeRequest.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderChangeRequest extends Model
{
    protected $fillable = [
        'order_id', 'item_id', 'new_quantity', 'reason', 'status'
    ];

    protected $casts = [
        'new_quantity' => 'double',
        'order_id' => 'integer',
        'item_id' => 'integer',
    ];

    // Add this relationship
    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}