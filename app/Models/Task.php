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
            $task->updateEstimateIfNeeded();
        });

        static::updated(function ($task) {
            $task->updateEstimateIfNeeded();
        });

        static::deleted(function ($task) {
            $task->updateEstimateIfNeeded();
        });
    }
    
    protected function updateEstimateIfNeeded(): void
    {
        if ($this->taskable_type === 'App\\Models\\Estimate') {
            $estimate = Estimate::find($this->taskable_id);
            $estimate?->recalculateTasksTotals();
        }
    }
}
