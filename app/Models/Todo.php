<?php

namespace App\Models;

use App\Traits\ModelHistoryTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Todo extends Model
{
    use SoftDeletes, ModelHistoryTrait;

    protected $table = 'todo';
    protected $appends = [
        'offer_type_name',
        'todo_complete_name',
        'read_name',
        'ago',
    ];

    // Accessor
    public function getOfferTypeNameAttribute() {
        $id = $this->offer_info_type;
        $offerTypes = config('constants.types');
        return $offerTypes[$id] ?? '';
    }

    public function getTodoCompleteNameAttribute() {
        $id = $this->todo_complete;
        $todo_completes = config('constants.completes');
        return $todo_completes[$id] ?? '';
    }

    public function getReadNameAttribute() {
        $id = $this->read;
        $reads = config('constants.reads');
        return $reads[$id] ?? '';
    }

    public function getAgoAttribute() {
        $created_at = $this->created_at;

        $created = strtotime($created_at);
        $now = strtotime("now");

        $diff = $now - $created;

        $diff_time = intval(($diff % 86400) / 3600);
        $diff_min = intval($diff_time / 60);
        $diff_sec = intval($diff_min / 60);
        $diff_days = $diff / 86400;

        if ($diff_days >= 365) {
            return ceil($diff_days / 365) . '年前';
        } elseif ($diff_days >= 30) {
            return ceil($diff_days / 30) . 'ヶ月前';
        } elseif ($diff_days >= 7) {
            return ceil($diff_days / 7) . '週前';
        } elseif ($diff_days >= 1) {
            return ceil($diff_days) . '日前';
        } elseif ($diff_time > 0) {
            return $diff_time . '時間前';
        } elseif ($diff_min > 0) {
            return $diff_min . '分前';
        } elseif ($diff_sec > 0) {
            return $diff_sec . '秒前';
        } else {
            return '数秒前';
        }

    }

}
