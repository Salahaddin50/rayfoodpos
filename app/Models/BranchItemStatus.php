<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BranchItemStatus extends Model
{
    protected $table = 'branch_item_statuses';

    protected $fillable = [
        'branch_id',
        'item_id',
        'status',
    ];

    protected $casts = [
        'id' => 'integer',
        'branch_id' => 'integer',
        'item_id' => 'integer',
        'status' => 'integer',
    ];

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function item(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}


