<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Job extends Model
{
    //


    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }


    protected $fillable = [
        'customer_id',
        'job_name',
        'site_address',
        'due_date',
        'status',
        'notes',
    ];
}
