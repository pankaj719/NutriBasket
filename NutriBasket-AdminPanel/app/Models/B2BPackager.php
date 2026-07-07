<?php

namespace App\Models;

use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class B2BPackager extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
    ];

    protected $hidden = [
        'password',
    ];

    protected $table = 'b2b_packagers';

    /**
     * Scope to get active packagers
     * Since the table doesn't have active/application_status columns,
     * we'll return all packagers as active by default
     */
    public function scopeActive($query)
    {
        return $query; // Return all packagers as active
    }
}
