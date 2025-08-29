<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'description',
        'price',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function taskable()
    {
        return $this->morphTo();
    }
    protected static function booted()
    {
        static::created(function ($task) {
            $task->updateParentTotals();
        });

        static::updated(function ($task) {
            $task->updateParentTotals();
        });

        static::deleted(function ($task) {
            $task->updateParentTotals();
        });
    }

    /**
     * Update totals on parent models if applicable
     */
    protected function updateParentTotals(): void
    {
        // If task belongs to an Estimate
        if ($this->taskable_type === Estimate::class) {
            $estimate = Estimate::find($this->taskable_id);
            $estimate?->recalculateTasksTotals();
        }

        // If task belongs to an Invoice
        if ($this->taskable_type === Invoice::class) {
            $invoice = Invoice::find($this->taskable_id);
            $invoice?->recalculateTasksTotals();
        }
    }
}
