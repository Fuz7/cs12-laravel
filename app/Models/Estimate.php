<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Estimate extends Model
{
    use HasFactory;

    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    protected $fillable = [
        'customer_id',
        'job_name',
        'status',
        'notes',
    ];
}
