<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Campaign extends Model
{
    use HasFactory;

    protected $table = "campaigns";
    
    protected $fillable = [
        'name',
        'slug',
        'description',
        'type',
        'discount_value',
        'free_item_id',
        'required_purchases',
        'status',
        'start_date',
        'end_date'
    ];

    protected $casts = [
        'id'                 => 'integer',
        'name'               => 'string',
        'slug'               => 'string',
        'description'        => 'string',
        'type'               => 'integer',
        'discount_value'     => 'decimal:6',
        'free_item_id'       => 'integer',
        'required_purchases' => 'integer',
        'status'             => 'integer',
        'start_date'         => 'datetime',
        'end_date'           => 'datetime',
    ];

    public function registrations(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CampaignRegistration::class, 'campaign_id', 'id');
    }

    public function freeItem(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Item::class, 'free_item_id', 'id');
    }
}
