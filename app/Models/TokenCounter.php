<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TokenCounter extends Model
{
    use HasFactory;

    protected $table = "token_counters";
    
    protected $fillable = [
        'branch_id',
        'shift_date',
        'counter',
        'prefix'
    ];

    protected $casts = [
        'id'         => 'integer',
        'branch_id'  => 'integer',
        'shift_date' => 'date',
        'counter'    => 'integer',
        'prefix'     => 'string',
    ];

    public function branch(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}



