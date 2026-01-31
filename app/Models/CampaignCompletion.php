<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CampaignCompletion extends Model
{
    use HasFactory;

    protected $fillable = [
        'campaign_id',
        'branch_id',
        'whatsapp',
        'completed_at',
        'final_order_id',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function finalOrder(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'final_order_id');
    }
}
