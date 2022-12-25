<?php

namespace App\Traits;

trait ModelHistoryTrait {

    protected static function booted()
    {
        $user_id = auth()->id();

        static::saving(function($model){

            $model->timestamps = false;

        });
        static::creating(function($model) use($user_id){

            $model->created_by = $user_id;
            $model->created_at = now();

        });
        static::updating(function($model) use($user_id){

            $model->updated_by = $user_id;
            $model->updated_at = now();

        });
        static::deleted(function($model) use($user_id){

            $model->deleted_by = $user_id;
            $model->saveQuietly(); // イベント実行なし

        });

    }

}