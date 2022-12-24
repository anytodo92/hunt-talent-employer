<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class QuestionAnswer extends Model
{
    protected $table = 'question_and_answers';

    protected $appends = [
        'question_type_name',
        'offer_info_type_name',
        'status_name',
        'ago',
    ];

    // Accessor
    public function getQuestionTypeNameAttribute() {
        $id = $this->question_type;
        $question_types = config('constants.question_type');
        return $question_types[$id] ?? '';
    }

    public function getOfferInfoTypeNameAttribute() {
        $id = $this->offer_info_type;
        $question_offer_info_types =  config('constants.question_offer_info_type');
        return $question_offer_info_types[$id] ?? '';
    }

    public function getStatusNameAttribute() {
        $id = $this->status;
        $question_statuses = config('constants.question_status');
        return $question_statuses[$id] ?? '';
    }

    public function getAgoAttribute() {

        if ($this->status == 1) {
            $updated_at = $this->question_datetime;
        } else {
            $updated_at = $this->answer_datetime;
        }

        $created = strtotime($updated_at);
        $now = strtotime("now");

        $diff = $now - $created;

        $diff_time = intval(($diff % 86400) / 3600);
        $diff_min = intval(($diff % 86400) / 60);
        $diff_sec = intval(($diff % 86400));
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
