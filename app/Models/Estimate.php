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

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function recalculateTasksTotals(): void
    {
        $totals = $this->tasks()
            ->selectRaw('COUNT(*) as count, COALESCE(SUM(price), 0) as total')
            ->first();

        $this->update([
            'tasks_total_price' => $totals->total ?? 0,
        ]);
    }
    protected $fillable = [
        'customer_id',
        'job_name',
        'status',
        'tasks_total_price',
        'notes',
    ];
}
