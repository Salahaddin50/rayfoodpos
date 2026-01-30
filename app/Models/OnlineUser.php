<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use App\Support\WhatsAppNormalizer;
use Illuminate\Database\Eloquent\Model;

class OnlineUser extends Model
{
    protected $table = 'online_users';

    protected $fillable = [
        'branch_id',
        'whatsapp',
        'location',
        'campaign_id',
        'last_order_id',
        'last_order_at',
    ];

    protected $casts = [
        'id'            => 'integer',
        'branch_id'     => 'integer',
        'campaign_id'   => 'integer',
        'last_order_id' => 'integer',
        'last_order_at' => 'datetime',
    ];

    public function campaign(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Campaign::class, 'campaign_id', 'id');
    }

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new BranchScope());
    }

    public function setWhatsappAttribute($value): void
    {
        $this->attributes['whatsapp'] = WhatsAppNormalizer::normalize($value);
    }
}


