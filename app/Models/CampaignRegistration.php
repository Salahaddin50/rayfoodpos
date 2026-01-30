<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignRegistration extends Model
{
    use HasFactory;

    protected $table = "campaign_registrations";
    
    protected $fillable = [
        'campaign_id',
        'name',
        'email',
        'phone',
        'verification_code',
        'status',
        'purchase_count',
        'rewards_claimed',
        'notes'
    ];

    protected $casts = [
        'id'              => 'integer',
        'campaign_id'     => 'integer',
        'name'            => 'string',
        'email'           => 'string',
        'phone'           => 'string',
        'status'          => 'integer',
        'purchase_count'  => 'integer',
        'rewards_claimed' => 'integer',
        'notes'           => 'string',
    ];

    public function campaign(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id', 'id');
    }
}
