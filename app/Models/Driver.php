<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use App\Support\WhatsAppNormalizer;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    protected $table = 'drivers';

    protected $fillable = [
        'branch_id',
        'name',
        'transport_type',
        'whatsapp',
        'status',
    ];

    protected $casts = [
        'id'        => 'integer',
        'branch_id' => 'integer',
        'status'    => 'integer',
    ];

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


