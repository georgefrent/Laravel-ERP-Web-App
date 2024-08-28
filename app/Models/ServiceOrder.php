<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceOrder extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

    use HasFactory;

    protected $fillable = [
        'device_name',
        'customer_name',
        'status',
        'progress',
        'price',
        'description',
        'payment_status',
        'entered_at',
        'started_at',
        'finished_at',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'entered_at' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];
}
