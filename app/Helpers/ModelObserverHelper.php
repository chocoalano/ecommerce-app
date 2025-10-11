<?php

namespace App\Helpers;

use Illuminate\Database\Eloquent\Model;
use App\Models\EventLog;
use Illuminate\Support\Facades\Auth;

class ModelObserverHelper
{
    /**
     * Log model event to event_logs table
     */
    public static function log(Model $model, string $eventType, array $extra = [])
    {
        EventLog::create([
            'user_id'    => Auth::id(),
            'event_type' => $eventType,
            'entity'     => $model->getTable(),
            'entity_id'  => $model->getKey(),
            'data_json'  => array_merge([
                'attributes' => $model->getAttributes(),
                'original'   => $model->getOriginal(),
            ], $extra),
            'created_at' => now(),
        ]);
    }

    /**
     * Attach observer to a model
     */
    public static function observe(Model $model)
    {
        $model::created(function ($m) {
            self::log($m, 'CREATED');
        });
        $model::updated(function ($m) {
            self::log($m, 'UPDATED');
        });
        $model::deleted(function ($m) {
            self::log($m, 'DELETED');
        });
    }
}
