<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Invoice extends Model

{

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
    //
    protected $fillable = [
        'customer_id',
        'job_name',
        'site_address',
        'due_date',
        'paid_amount',
        'status',
        'tasks_total_price',
        'notes',
    ];
}
