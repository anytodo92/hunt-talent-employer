<?php

namespace App\Http\Controllers\Outsource;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function __construct()
    {
    }

    public function index()
    {
        return view('outsource.home');
    }

    public function job_list()
    {
        var_dump('人材紹介の求人の一覧画面です。<br>_____まだ準備中です。_____');
        exit;
    }

    public function job_list_search($type)
    {
        if ($type == 'J') {
            var_dump('人材紹介の求人の検索画面です。<br>_____まだ準備中です。_____');
            exit;
        } elseif ($type == 'G') {
            var_dump('業務委託案件の検索画面です。<br>_____まだ準備中です。_____');
            exit;
        }
    }

    public function job_list_count($type)
    {
        if ($type == 'J') {
            var_dump('人材紹介の求人のカウント画面です。<br>_____まだ準備中です。_____');
            exit;
        } elseif ($type == 'G') {
            var_dump('業務委託案件のカウント画面です。<br>_____まだ準備中です。_____');
            exit;
        }
    }

    public function job_public($type, $id)
    {
        if ($type == 'J') {
            var_dump('人材紹介の求人の公開画面です。<br>_____まだ準備中です。_____');
            exit;
        } elseif ($type == 'G') {
            var_dump('業務委託案件の公開画面です。<br>_____まだ準備中です。_____');
            exit;
        }
    }

    public function job_stop($type, $id)
    {
        if ($type == 'J') {
            var_dump('人材紹介の求人の非公開画面です。<br>_____まだ準備中です。_____');
            exit;
        } elseif ($type == 'G') {
            var_dump('業務委託案件の非公開画面です。<br>_____まだ準備中です。_____');
            exit;
        }
    }

    public function job_add($type)
    {
        if ($type == 'J') {
            var_dump('人材紹介の求人の新規画面です。<br>_____まだ準備中です。_____');
            exit;
        } elseif ($type == 'G') {
            var_dump('業務委託案件の新規画面です。<br>_____まだ準備中です。_____');
            exit;
        }
    }

    public function job_edit($type, $id)
    {
        if ($type == 'J') {
            var_dump('人材紹介の求人の編集画面です。<br>_____まだ準備中です。_____');
            exit;
        } elseif ($type == 'G') {
            var_dump('業務委託案件の編集画面です。<br>_____まだ準備中です。_____');
            exit;
        }
    }

    public function job_show($type, $id)
    {
        if ($type == 'J') {
            var_dump('人材紹介の求人の詳細画面です。<br>_____まだ準備中です。_____');
            exit;
        } elseif ($type == 'G') {
            var_dump('業務委託案件の詳細画面です。<br>_____まだ準備中です。_____');
            exit;
        }
    }


    public function job_qa($type, $id)
    {
        if ($type == 'J') {
            var_dump('人材紹介の求人のQ&A画面です。<br>_____まだ準備中です。_____');
            exit;
        } elseif ($type == 'G') {
            var_dump('業務委託案件のQ&A画面です。<br>_____まだ準備中です。_____');
            exit;
        }
    }

}
