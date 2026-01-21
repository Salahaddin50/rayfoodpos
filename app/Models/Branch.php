<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory;
    protected $table = "branches";
    protected $fillable = [
        'name',
        'email',
        'phone',
        'latitude',
        'longitude',
        'city',
        'state',
        'zip_code',
        'address',
        'status',
        // Delivery rules (branch-specific)
        'free_delivery_threshold',
        'delivery_distance_threshold_1',
        'delivery_distance_threshold_2',
        'delivery_cost_1',
        'delivery_cost_2',
        'delivery_cost_3',
    ];
    protected $casts = [
        'id'        => 'integer',
        'name'      => 'string',
        'email'     => 'string',
        'phone'     => 'string',
        'latitude'  => 'string',
        'longitude' => 'string',
        'city'      => 'string',
        'state'     => 'string',
        'zip_code'  => 'string',
        'address'   => 'string',
        'status'    => 'integer',
        'free_delivery_threshold' => 'decimal:2',
        'delivery_distance_threshold_1' => 'decimal:2',
        'delivery_distance_threshold_2' => 'decimal:2',
        'delivery_cost_1' => 'decimal:2',
        'delivery_cost_2' => 'decimal:2',
        'delivery_cost_3' => 'decimal:2',
    ];
}
