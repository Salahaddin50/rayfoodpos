<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Model;

class OnlineUser extends Model
{
    protected $table = 'online_users';

    protected $fillable = [
        'branch_id',
        'whatsapp',
        'location',
        'last_order_id',
        'last_order_at',
    ];

    protected $casts = [
        'id'            => 'integer',
        'branch_id'     => 'integer',
        'last_order_id' => 'integer',
        'last_order_at' => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(new BranchScope());
    }
}


