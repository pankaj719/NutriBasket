<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class B2BClient extends Model
{
    protected $table = 'b2b_clients';
    protected $fillable = ['name', 'default_deliveryman_id', 'address'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'b2b_client_user', 'b2b_client_id', 'user_id');
    }

    public function contracts()
    {
        return $this->hasMany(\App\Models\Contract::class, 'client_id');
    }

    public function defaultDeliveryman()
    {
        return $this->belongsTo(\App\Models\DeliveryMan::class, 'default_deliveryman_id');
    }

    public function defaultPackager()
    {
        return $this->belongsTo(\App\Models\B2BPackager::class, 'default_packager_id');
    }
}