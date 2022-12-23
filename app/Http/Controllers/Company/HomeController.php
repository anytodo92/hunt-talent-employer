<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Mail\CompanyRequested;
use App\Mail\QaAnswered;
use App\Models\InterviewSchedule;
use App\Models\User;
use App\Models\CompanyUser;
use App\Models\OutsourceCompanyUser;
use App\Models\OutsourceOfferInfo;
use App\Models\QuestionAnswer;
use App\Models\RecruitCompanyUser;
use App\Models\RecruitOfferInfo;
use App\Models\Timeline;
use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Mail;


class HomeController extends Controller
{
    protected $corpoartions_id;
    protected $headInfo;

    public function __construct()
    {
    }

    public function index()
    {
        return view('company.home');

    }

    public function chat_detail($id)
    {
        $timeline = Timeline::find($id);
        $timeline->read_flg = 2;
        $timeline->save();

        var_dump('_____まだ準備中です。_____');
        exit;
    }

    public function todo_detail($id)
    {
        $todo = Todo::find($id);
        $todo->read_flg = 2;
        $todo->save();

        var_dump('_____まだ準備中です。_____');
        exit;
    }

    public function calendar_detail($id)
    {
        $calendar = InterviewSchedule::find($id);
        $calendar->save();

        var_dump('_____まだ準備中です。_____');
        exit;
    }

    public function job_list()
    {
		$authUser = Auth::user();
        $corpoartions_id = $authUser->company_user()->first()->company_id;
        
        $recruitOfferInfos = RecruitOfferInfo::where('company_id', $corpoartions_id)->get();
        $outsourceOfferInfos = OutsourceOfferInfo::where('company_id', $corpoartions_id)->get();

        $result = [];
        $count_open_status = [];

        $open_statuses = config('constants.open_status');
        $str_open_status[0] = '全て';
        $count_open_status[0] = 0;
        foreach ($open_statuses as $id => $status) {
            $count_open_status[$id] = 0;
            $str_open_status[$id] = $status;
        }

        $r1 = $this->getResultFromRecruitOfferInfos($recruitOfferInfos, $count_open_status);

        $result = array_merge($result, $r1['list']);
        $count_open_status = $r1['count'];


        $r2 = $this->getResultFromOutsourceOfferInfos($outsourceOfferInfos, $count_open_status);

        $result = array_merge($result, $r2['list']);
        $count_open_status = $r2['count'];

        return view('company.job_list')->with(compact('result', 'count_open_status', 'str_open_status'));
    }

    public function job_list_search($type)
    {
		$authUser = Auth::user();
        $corpoartions_id = $authUser->company_user()->first()->company_id;
        
        $keyword = $_GET['keyword'];
        $keyword = str_replace('　', ' ', $keyword);

        $arr_keyword = explode(" ", $keyword);
        $open_status = $_GET['open_status'];
        if ($type == 0) {
            $tableContent = array(
                // "draw" => 0, // for every request/draw by clientside , they send a number as a parameter, when they recieve a response/data they first check the draw number, so we are sending same number in draw.
                "recordsTotal" => 0, // total number of records
                "recordsFiltered" => 0, // total number of records after searching, if there is no searching then totalFiltered = totalData
                "data" => []
            );
            return $tableContent;
        }

        $result = [];

        $arr_open_status = config('constants.open_status');

        $arr_category_1 = config('constants.category_1');
        $arr_category_2 = config('constants.category_2');

        if ($type == 1 || $type == 3) {
            $recruitOfferInfos = new RecruitOfferInfo();
            
            $recruitOfferInfos = $recruitOfferInfos->where('company_id', $corpoartions_id);
            
            foreach ($arr_keyword as $str) {
                if (trim($str) == '') continue;
                $date_str = str_replace('/', '-', $str);
                $date_start = $date_str . ' 00:00:00';
                $date_end = $date_str . ' 23:59:59';

                $open_status_ids = [];
                foreach ($arr_open_status as $id=>$text) {
                    if (strpos($text, $str) !== false) $open_status_ids[] = $id;
                }

                $firstC = strtoupper($str[0]);
                if ($firstC == 'G') {
                    $str_id = 0;
                } elseif ($firstC == 'J') {
                    $str_id = substr($str, 1);
                    if ($str_id == '') continue;
                } else {
                    $str_id = $str;
                }

                $str_count = intval($str) == 0 ? -1 : intval($str);

                $category_1_ids = [];
                foreach ($arr_category_1 as $id => $category) {
                    if (strpos($category, $str) !== false) $category_1_ids[] = $id;
                }

                $category_2_ids = [];
                foreach ($arr_category_2 as $id1 => $arr_category) {
                    foreach ($arr_category as $id2 => $category) {
                        if (strpos($category, $str) !== false) $category_2_ids[] = compact('id1', 'id2');
                    }
                }

                $recruitOfferInfos = $recruitOfferInfos->where(function($query) use ($str, $date_start, $date_end, $open_status_ids, $str_id, $str_count, $category_1_ids, $category_2_ids) {
                    $query = $query->where('job_title', 'LIKE', '%' . $str . '%')
                        ->orWhere('id', $str_id)
                        ->orWhereIn('open_status', $open_status_ids)
                        ->orWhereIn('occupation_category_1', $category_1_ids);


                    foreach($category_2_ids as $category_2_id) {
                        $id1 = $category_2_id['id1'];
                        $id2 = $category_2_id['id2'];
                        $query = $query->orWhere(function($query2) use ($id1, $id2) {
                            $query2->where('occupation_category_1', $id1)->where('occupation_category_2', $id2);
                        });
                    }
                    $query->orWhereBetween('created_at', [$date_start, $date_end])
                        ->orWhereBetween('updated_at', [$date_start, $date_end])
                        ->orHas('recruitJobSeekerApplyMgts', $str_count);
                });
            }

            if ($open_status > 0) {
                $recruitOfferInfos = $recruitOfferInfos->where('open_status', $open_status);
            }

            $recruitOfferInfos = $recruitOfferInfos->get();


            $r1 = $this->getResultFromRecruitOfferInfos($recruitOfferInfos);
            $result = array_merge($result, $r1['list']);

        }

        if ($type == 2 || $type == 3) {

            $outsourceOfferInfos = new OutsourceOfferInfo();
            
            $outsourceOfferInfos = $outsourceOfferInfos->where('company_id', $corpoartions_id);
            
            foreach ($arr_keyword as $str) {
                if (trim($str) == '') continue;
                $date_str = str_replace('/', '-', $str);
                $date_start = $date_str . ' 00:00:00';
                $date_end = $date_str . ' 23:59:59';

                $open_status_ids = [];
                foreach ($arr_open_status as $id=>$text) {
                    if (strpos($text, $str) !== false) $open_status_ids[] = $id;
                }

                $firstC = strtoupper($str[0]);
                if ($firstC == 'J') {
                    $str_id = 0;
                } elseif ($firstC == 'G') {
                    $str_id = substr($str, 1);
                    if ($str_id == '') continue;
                } else {
                    $str_id = $str;
                }

                $str_count = intval($str) == 0 ? -1 : intval($str);

                $category_1_ids = [];
                foreach ($arr_category_1 as $id => $category) {
                    if (strpos($category, $str) !== false) $category_1_ids[] = $id;
                }

                $category_2_ids = [];
                foreach ($arr_category_2 as $id1 => $arr_category) {
                    foreach ($arr_category as $id2 => $category) {
                        if (strpos($category, $str) !== false) $category_2_ids[] = compact('id1', 'id2');
                    }
                }

                $outsourceOfferInfos = $outsourceOfferInfos->where(function($query) use ($str, $date_start, $date_end, $open_status_ids, $str_id, $str_count, $category_1_ids, $category_2_ids) {
                    $query = $query->where('job_title', 'LIKE', '%' . $str . '%')
                        ->orWhere('id', $str_id)
                        ->orWhereIn('open_status', $open_status_ids)
                        ->orWhereIn('occupation_category_1', $category_1_ids);


                    foreach($category_2_ids as $category_2_id) {
                        $id1 = $category_2_id['id1'];
                        $id2 = $category_2_id['id2'];
                        $query = $query->orWhere(function($query2) use ($id1, $id2) {
                            $query2->where('occupation_category_1', $id1)->where('occupation_category_2', $id2);
                        });
                    }
                    $query->orWhereBetween('created_at', [$date_start, $date_end])
                        ->orWhereBetween('updated_at', [$date_start, $date_end])
                        ->orHas('outsourceJobSeekerApplyMgts', $str_count);
                });

            }

            if ($open_status > 0) {
                $outsourceOfferInfos = $outsourceOfferInfos->where('open_status', $open_status);
            }

            $outsourceOfferInfos = $outsourceOfferInfos->get();


            $r2 = $this->getResultFromOutsourceOfferInfos($outsourceOfferInfos);
            $result = array_merge($result, $r2['list']);
        }
        $data = [];
        foreach ($result as $ret) {
            $html = [];
            $html[0] = '<div class="recruit_title_td"><div class="left"><img src="' . $ret['image_main'] . '" /></div>';
            $html[0] .= '<div class="right"><p class="title">' . $ret['title'] . '</p>';
            $html[0] .= '<p>' . $ret['full_category'] . '</p>';
            $html[0] .= '<p>' . $ret['type'] . ' 求人ID：' . $ret['id_text'] . '</p>';
            $html[0] .= '</div></div>';

            $html[1] = $ret['open_status_text'];
            if ($ret['open_status'] == 3 || $ret['open_status'] == 4) {
                if ($ret['t'] == 'J') {
                    $html[1] .= '<br><a href="#" data-bs-toggle="modal" data-bs-target="#recruitPublicModal" data-id="' . $ret['id'] . '" data-type="' . $ret['t'] . '" data-title="' . $ret['title'] . '" data-full_category="' . $ret['full_category'] . '" data-method="' .  $ret['method'] . '" data-income="' . $ret['income'] . '" data-ideal_income="' . $ret['ideal_income'] .'" data-refund="' . $ret['refund'] . '" data-fixed_reward ="' . $ret['fixed_reward'] . '" class="recruitPublicModal">公開</a>';
                } elseif ($ret['t'] == 'G') {
                    $html[1] .= '<br><a href="#" data-bs-toggle="modal" data-bs-target="#outsourcePublicModal" data-id="' . $ret['id'] . '" data-type="' . $ret['t'] .'" data-title="' . $ret['title'] . '" data-full_category="' . $ret['full_category'] . '" data-unit_price="' . $ret['unit_price'] . '" class="outsourcePublicModal">公開</a>';
                }
            } elseif ($ret['open_status'] == 1) {
                $html[1] .= '<br><a href="#" data-bs-toggle="modal" data-bs-target="#stopModal" class="stopRecruit" data-id="' . $ret['id'] . '" data-type="' . $ret['t'] . '">募集停止</a>';
            }

            $html[2] = '<p>' . $ret['updated_at_date'] . '</p>';
            $html[2] .= '(<span>' . $ret['created_at_date'] . '作成</span>)';

            $html[3] = $ret['selection_count'];
            if($ret['open_status'] == 2) {
                $html[4] = '<a href="' . route('company.job_edit', ['type' => $ret['t'], 'id' => $ret['id']]) . '" class="job_list_edit job_list_disabled">編集</a>';
            } else {
                $html[4] = '<a href="' . route('company.job_edit', ['type' => $ret['t'], 'id' => $ret['id']]) . '" class="job_list_edit">編集</a>';
            }


            $html[5] = '<a href="' . route('company.job_show', ['type' => $ret['t'], 'id' => $ret['id']]) . '">表示</a>';

            $data[] = $html;
        }

        $tableContent = array(
            "recordsTotal" => count($result), // total number of records
            "recordsFiltered" => count($result), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data
        );
        return $tableContent;
    }

    public function job_list_count($type)
    {
    	$authUser = Auth::user();
        $corpoartions_id = $authUser->company_user()->first()->company_id;
        
        $keyword = $_GET['keyword'];
        $keyword = str_replace('　', ' ', $keyword);

        $arr_keyword = explode(" ", $keyword);
        $open_status = $_GET['open_status'];

        $open_statuses = config('constants.open_status');
        $str_open_status[0] = '全て';
        $count_open_status[0] = 0;
        foreach ($open_statuses as $id => $status) {
            $count_open_status[$id] = 0;
            $str_open_status[$id] = $status;
        }

        $arr_open_status = config('constants.open_status');

        $arr_category_1 = config('constants.category_1');
        $arr_category_2 = config('constants.category_2');

        if ($type == 1 || $type == 3) {

            $recruitOfferInfos = new RecruitOfferInfo();
            
            $recruitOfferInfos = $recruitOfferInfos->where('company_id', $corpoartions_id);
            
            foreach ($arr_keyword as $str) {
                if (trim($str) == '') continue;
                $date_str = str_replace('/', '-', $str);
                $date_start = $date_str . ' 00:00:00';
                $date_end = $date_str . ' 23:59:59';

                $open_status_ids = [];
                foreach ($arr_open_status as $id=>$text) {
                    if (strpos($text, $str) !== false) $open_status_ids[] = $id;
                }

                $firstC = strtoupper($str[0]);
                if ($firstC == 'G') {
                    $str_id = 0;
                } elseif ($firstC == 'J') {
                    $str_id = substr($str, 1);
                    if ($str_id == '') continue;
                } else {
                    $str_id = $str;
                }

                $str_count = intval($str) == 0 ? -1 : intval($str);

                $category_1_ids = [];
                foreach ($arr_category_1 as $id => $category) {
                    if (strpos($category, $str) !== false) $category_1_ids[] = $id;
                }

                $category_2_ids = [];
                foreach ($arr_category_2 as $id1 => $arr_category) {
                    foreach ($arr_category as $id2 => $category) {
                        if (strpos($category, $str) !== false) $category_2_ids[] = compact('id1', 'id2');
                    }
                }

                $recruitOfferInfos = $recruitOfferInfos->where(function($query) use ($str, $date_start, $date_end, $open_status_ids, $str_id, $str_count, $category_1_ids, $category_2_ids) {
                    $query = $query->where('job_title', 'LIKE', '%' . $str . '%')
                        ->orWhere('id', $str_id)
                        ->orWhereIn('open_status', $open_status_ids)
                        ->orWhereIn('occupation_category_1', $category_1_ids);


                    foreach($category_2_ids as $category_2_id) {
                        $id1 = $category_2_id['id1'];
                        $id2 = $category_2_id['id2'];
                        $query = $query->orWhere(function($query2) use ($id1, $id2) {
                            $query2->where('occupation_category_1', $id1)->where('occupation_category_2', $id2);
                        });
                    }
                    $query->orWhereBetween('created_at', [$date_start, $date_end])
                        ->orWhereBetween('updated_at', [$date_start, $date_end])
                        ->orHas('recruitJobSeekerApplyMgts', $str_count);
                });
            }

            if ($open_status > 0) {
                $recruitOfferInfos = $recruitOfferInfos->where('open_status', $open_status);
            }

            $recruitOfferInfos = $recruitOfferInfos->get();


            foreach ($recruitOfferInfos as $recruitOfferInfo) {
                $count_open_status[0]++;
                if ($recruitOfferInfo->open_status > 0 && $recruitOfferInfo->open_status < count($count_open_status)) {
                    $count_open_status[$recruitOfferInfo->open_status]++;
                }
            }

        }

        if ($type == 2 || $type == 3) {
            $outsourceOfferInfos = new OutsourceOfferInfo();
            
            $outsourceOfferInfos = $outsourceOfferInfos->where('company_id', $corpoartions_id);
            
            foreach ($arr_keyword as $str) {
                if (trim($str) == '') continue;

                $date_str = str_replace('/', '-', $str);
                $date_start = $date_str . ' 00:00:00';
                $date_end = $date_str . ' 23:59:59';

                $open_status_ids = [];
                foreach ($arr_open_status as $id=>$text) {
                    if (strpos($text, $str) !== false) $open_status_ids[] = $id;
                }

                $firstC = strtoupper($str[0]);
                if ($firstC == 'J') {
                    $str_id = 0;
                } elseif ($firstC == 'G') {
                    $str_id = substr($str, 1);
                    if ($str_id == '') continue;
                } else {
                    $str_id = $str;
                }

                $str_count = intval($str) == 0 ? -1 : intval($str);

                $category_1_ids = [];
                foreach ($arr_category_1 as $id => $category) {
                    if (strpos($category, $str) !== false) $category_1_ids[] = $id;
                }

                $category_2_ids = [];
                foreach ($arr_category_2 as $id1 => $arr_category) {
                    foreach ($arr_category as $id2 => $category) {
                        if (strpos($category, $str) !== false) $category_2_ids[] = compact('id1', 'id2');
                    }
                }

                $outsourceOfferInfos = $outsourceOfferInfos->where(function($query) use ($str, $date_start, $date_end, $open_status_ids, $str_id, $str_count, $category_1_ids, $category_2_ids) {
                    $query = $query->where('job_title', 'LIKE', '%' . $str . '%')
                        ->orWhere('id', $str_id)
                        ->orWhereIn('open_status', $open_status_ids)
                        ->orWhereIn('occupation_category_1', $category_1_ids);


                    foreach($category_2_ids as $category_2_id) {
                        $id1 = $category_2_id['id1'];
                        $id2 = $category_2_id['id2'];
                        $query = $query->orWhere(function($query2) use ($id1, $id2) {
                            $query2->where('occupation_category_1', $id1)->where('occupation_category_2', $id2);
                        });
                    }
                    $query->orWhereBetween('created_at', [$date_start, $date_end])
                        ->orWhereBetween('updated_at', [$date_start, $date_end])
                        ->orHas('outsourceJobSeekerApplyMgts', $str_count);
                });
            }

            if ($open_status > 0) {
                $outsourceOfferInfos = $outsourceOfferInfos->where('open_status', $open_status);
            }

            $outsourceOfferInfos = $outsourceOfferInfos->get();

            foreach ($outsourceOfferInfos as $outsourceOfferInfo) {
                $count_open_status[0]++;
                if ($outsourceOfferInfo->open_status > 0 && $outsourceOfferInfo->open_status < count($count_open_status)) {
                    $count_open_status[$outsourceOfferInfo->open_status]++;
                }
            }
        }


        $html = '';
        foreach ($count_open_status as $id => $count) {
            if ($id == $open_status) {
                $html .= '<span style="font-weight: bold" class="search_status" data-id="' .  $id . '">' .  (isset($str_open_status[$id]) ? $str_open_status[$id] : '') . '&nbsp;' . $count . '&nbsp;&nbsp;</span>';
            } else {
                $html .= '<span class="search_status" data-id="' .  $id . '">' .  (isset($str_open_status[$id]) ? $str_open_status[$id] : '') . '&nbsp;' . $count . '&nbsp;&nbsp;</span>';
            }
        }

        return response()->json(array('success' => true, 'html' => $html));

    }

    public function job_public($type, $id)
    {
        if ($type == 'J') {
            $recruitOfferInfo = RecruitOfferInfo::find($id);
            if ($recruitOfferInfo->open_status == 3) {

                if (!empty(strtotime($recruitOfferInfo->public_at)) && strtotime($recruitOfferInfo->public_at) < strtotime($recruitOfferInfo->updated_at)) {
                    $recruitOfferInfo->open_status = 2;
                    $open_status = 2;
                } else {
                    $recruitOfferInfo->open_status = 1;
                    $open_status = 1;
                }
            } elseif ($recruitOfferInfo->open_status == 4) {
                $recruitOfferInfo->open_status = 2;
                $open_status = 2;
            }


            $recruitOfferInfo->save();

        } elseif ($type == 'G') {
            $outsourceOfferInfo = OutsourceOfferInfo::find($id);
            if ($outsourceOfferInfo->open_status == 3) {
                if (!empty(strtotime($outsourceOfferInfo->public_at)) && strtotime($outsourceOfferInfo->public_at) < strtotime($outsourceOfferInfo->updated_at)) {
                    $outsourceOfferInfo->open_status = 2;
                    $open_status = 2;
                } else {
                    $outsourceOfferInfo->open_status = 1;
                    $open_status = 1;
                }
            } elseif ($outsourceOfferInfo->open_status == 4) {
                $outsourceOfferInfo->open_status = 2;
                $open_status = 2;
            }

            $outsourceOfferInfo->save();
        }

        return response()->json(array('success' => true, 'id' => $id, 'type' => $type, 'open_status' => $open_status));
    }

    public function job_stop($type, $id)
    {

        if ($type == 'J') {
            $recruitOfferInfo = RecruitOfferInfo::find($id);
            $recruitOfferInfo->open_status = 3;
            $recruitOfferInfo->public_at = date('Y-m-d H:i:s');
            $recruitOfferInfo->save();

            $method = $recruitOfferInfo->success_reward_calculation_method;
            $title = $recruitOfferInfo->job_title;
            $full_category = $recruitOfferInfo->full_category;
            $income = $recruitOfferInfo->theory_annual_income ? $recruitOfferInfo->theory_annual_income : '';
            $refund = $recruitOfferInfo->refund_policy ? $recruitOfferInfo->refund_policy : '';
            $fixed_reward = $recruitOfferInfo->fixed_reward ? $recruitOfferInfo->fixed_reward : '';
            $ideal_income = $recruitOfferInfo->theory_annual_income_definition ? $recruitOfferInfo->theory_annual_income_definition : '';

            $unit_price = '';
        } elseif ($type == 'G') {
            $outsourceOfferInfo = OutsourceOfferInfo::find($id);
            $outsourceOfferInfo->open_status = 3;
            $outsourceOfferInfo->public_at = date('Y-m-d H:i:s');
            $outsourceOfferInfo->save();

            $method = $outsourceOfferInfo->success_reward_calculation_method;
            $title = $outsourceOfferInfo->job_title;
            $full_category = $outsourceOfferInfo->full_category;
            $income = $outsourceOfferInfo->theory_annual_income ? $outsourceOfferInfo->theory_annual_income : '';
            $refund = $outsourceOfferInfo->refund_policy ? $outsourceOfferInfo->refund_policy : '';
            $fixed_reward = $outsourceOfferInfo->fixed_reward ? $outsourceOfferInfo->fixed_reward : '';
            $ideal_income = $outsourceOfferInfo->theory_annual_income_definition ? $outsourceOfferInfo->theory_annual_income_definition : '';
            $unit_price = $outsourceOfferInfo->unit_price ? $outsourceOfferInfo->unit_price : '';

        }

        $success = true;
        return response()->json(compact('success', 'id', 'type', 'method', 'title', 'full_category', 'income', 'refund', 'fixed_reward', 'ideal_income', 'unit_price'));
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

    private function getResultFromRecruitOfferInfos($recruitOfferInfos, $count_open_status = []) {
        $result = [];
        foreach ($recruitOfferInfos as $recruitOfferInfo) {

            if ($recruitOfferInfo->image_main == '') {
                $image_main = config('constants.no_image_url');
            } else {
                $image_main = '/storage/' . $recruitOfferInfo->company_id . '/' . $recruitOfferInfo->image_main;
            }

            $ret = array(
                'title' => $recruitOfferInfo->job_title,
                'image_main' => $image_main,
                //'image_main' => '/storage/' . $recruitOfferInfo->company_id . '/' . $recruitOfferInfo->image_main,
                'full_category' => $recruitOfferInfo->full_category,
                'open_status' => $recruitOfferInfo->open_status,
                'open_status_text' => $recruitOfferInfo->open_status_text,
                'selection_count' => $recruitOfferInfo->selection_count,
                'type' => '人材紹介の求人',
                'id' => $recruitOfferInfo->id,
                't' => 'J',
                'method' => $recruitOfferInfo->success_reward_calculation_method,
                'income' => $recruitOfferInfo->theory_annual_income,
                'refund' => $recruitOfferInfo->refund_policy,
                'fixed_reward' => $recruitOfferInfo->fixed_reward,
                'ideal_income' => $recruitOfferInfo->theory_annual_income_definition,
                'id_text' => 'J' . sprintf("%06d", $recruitOfferInfo->id),
                'created_at_date' => date('Y.m.d', strtotime($recruitOfferInfo->created_at)),
                'updated_at_date' => date('Y.m.d', strtotime($recruitOfferInfo->updated_at)),

            );
            if (count($count_open_status) > 0) {
                $count_open_status[0]++;
                if ($recruitOfferInfo->open_status > 0 && $recruitOfferInfo->open_status < count($count_open_status)) {
                    $count_open_status[$recruitOfferInfo->open_status]++;
                }
            }


            $result[] = $ret;
        }

        return array('list' => $result, 'count' => $count_open_status);
    }

    private function getResultFromOutsourceOfferInfos($outsourceOfferInfos, $count_open_status = []) {
        $result = [];
        foreach ($outsourceOfferInfos as $outsourceOfferInfo) {

            if ($outsourceOfferInfo->image_main == '') {
                $image_main = config('constants.no_image_url');
            } else {
                $image_main = '/storage/' . $outsourceOfferInfo->company_id . '/' . $outsourceOfferInfo->image_main;
            }

            $ret = array(
                'title' => $outsourceOfferInfo->job_title,
                'image_main' => $image_main,
                //'image_main' => '/storage/' . $outsourceOfferInfo->company_id . '/' . $outsourceOfferInfo->image_main,
                'full_category' => $outsourceOfferInfo->full_category,
                'open_status' => $outsourceOfferInfo->open_status,
                'open_status_text' => $outsourceOfferInfo->open_status_text,
                'selection_count' => $outsourceOfferInfo->selection_count,
                'type' => '業務委託案件',
                'id' => $outsourceOfferInfo->id,
                't' => 'G',
                'method' => $outsourceOfferInfo->success_reward_calculation_method,
                'income' => $outsourceOfferInfo->theory_annual_income,
                'refund' => $outsourceOfferInfo->refund_policy,
                'fixed_reward' => $outsourceOfferInfo->fixed_reward,
                'ideal_income' => $outsourceOfferInfo->theory_annual_income_definition,
                'unit_price' => $outsourceOfferInfo->unit_price,
                'id_text' => 'G' . sprintf("%06d", $outsourceOfferInfo->id),
                'created_at_date' => date('Y.m.d', strtotime($outsourceOfferInfo->created_at)),
                'updated_at_date' => date('Y.m.d', strtotime($outsourceOfferInfo->updated_at)),
            );
            if (count($count_open_status) > 0) {
                $count_open_status[0]++;
                if ($outsourceOfferInfo->open_status > 0 && $outsourceOfferInfo->open_status < count($count_open_status)) {
                    $count_open_status[$outsourceOfferInfo->open_status]++;
                }
            }
            $result[] = $ret;
        }

        return array('list' => $result, 'count' => $count_open_status);
    }


    public function qa_list() {

        $authUser = Auth::user();
        $answer_person_id = $authUser->company_user()->first()->id;

        $corpoartions_id = $authUser->company_user()->first()->company_id;
        $questionAnswers = QuestionAnswer::where('company_id', $corpoartions_id)->orderBy('question_datetime', 'desc')->get();

        $result = [];
        $count_question_status = [];

        $question_statuses = config('constants.question_status');

        $str_question_status[0] = '全て';
        $count_question_status[0] = 0;
        foreach ($question_statuses as $id => $status) {
            $count_question_status[$id] = 0;
            $str_question_status[$id] = $status;
        }

        $r1 = $this->getResultFromQuestionAnswers($questionAnswers, $count_question_status);

        $result = array_merge($result, $r1['list']);
        $count_question_status = $r1['count'];


        return view('company.qa_list')->with(compact('result', 'count_question_status', 'str_question_status', 'answer_person_id'));

    }

    public function qa_list_search()
    {

        $authUser = Auth::user();
        $answer_person_id = $authUser->company_user()->first()->id;
		
		$corpoartions_id = $authUser->company_user()->first()->company_id;
		
        $type = $_GET['type'];
        $sort = $_GET['sort'];
        $status = $_GET['status'];

        $questionAnswers = QuestionAnswer::where('company_id', $corpoartions_id);

        if ($type > 0) {
            $questionAnswers = $questionAnswers->where('question_type', $type);
        }

        if ($status > 0) {
            $questionAnswers = $questionAnswers->where('status', $status);
        }

        if ($sort == 0) {
            $questionAnswers = $questionAnswers->orderBy('question_datetime', 'desc');
        } elseif ($sort == 1) {
            $questionAnswers = $questionAnswers->orderBy('question_datetime', 'asc');
        } elseif ($sort == 2) {
            $questionAnswers = $questionAnswers->orderBy('answer_datetime', 'desc');
        } elseif ($sort == 3) {
            $questionAnswers = $questionAnswers->orderBy('answer_datetime', 'asc');
        }

        $questionAnswers = $questionAnswers->get();


        $count_question_status = [];

        $question_statuses = config('constants.question_status');

        $str_question_status[0] = '全て';
        $count_question_status[0] = 0;
        foreach ($question_statuses as $id => $status) {
            $count_question_status[$id] = 0;
            $str_question_status[$id] = $status;
        }

        $r1 = $this->getResultFromQuestionAnswers($questionAnswers, $count_question_status);

        $result = $r1['list'];

        $data = [];
        foreach ($result as $ret) {
            $html = [];

            $html[0] = '<div id="td-' . $ret['id'] . '">';
            $html[0] .= '<div class="qa_table_cell_container">';
            $html[0] .= '<div class="qa_table_accordion">';
            if ($ret['status'] == 2) {
                $html[0] .= '<input type="checkbox" checked><i></i>';
            } elseif ($ret['status'] == 1) {
                $html[0] .= '<input type="checkbox"><i></i>';
            }


            $html[0] .= '<div class="qa_title">';

            $html[0] .= '<div class="qa_title_status">';

            if ($ret['status'] == 2) {
                $html[0] .= '<div class="qa_title_status_panel qa_title_status_panel_finish">' . $ret['status_name'] . '</div>';
            } elseif ($ret['status'] == 1) {
                $html[0] .= '<div class="qa_title_status_panel qa_title_status_panel_waiting">' . $ret['status_name'] . '</div>';
            }
            $html[0] .= '<div class="qa_title_status_text">' . $ret['ago'] .'</div>';
            $html[0] .= '</div>';


            $html[0] .= '<div class="qa_title_container">';
            $html[0] .= '<p class="qa_title_type">【' . $ret['offer_info_type_name'] . '】</p>';
            $html[0] .= '<p class="qa_title_title">【' . $ret['question_title'] . '】</p>';
            $html[0] .= '<p class="qa_title_job_title"><a href="' . route('company.job_qa', ['type' => $ret['job_type'], 'id' => $ret['job_id']]) . '"><i class="ti-pencil-alt"></i>' .  $ret['job_title'] . '</a></p>';
            $html[0] .= '</div>';

            $html[0] .= '</div>';



            $html[0] .= '<div class="qa_contents">';

            $html[0] .= '<div class="qa_contents_item">';
            $html[0] .= '<div class="qa_contents_item_logo"><img src="' . $ret['question_logo'] . '" onerror="onImgErr(this)"></div>';
            $html[0] .= '<div class="qa_contents_item_container">';
            $html[0] .= '<p class="qa_contents_item_company_title">' . $ret['question_name'] . '</p>';
            $html[0] .= '<p class="qa_contents_item_datetime">投稿日：' . date('Y.m.d H:i', strtotime($ret['question_datetime'])) . '</p>';
            $html[0] .= '<span class="qa_contents_item_status">' . $ret['question_type_name'] . '</span>';
            $html[0] .= '<p class="qa_contents_item_text">' . nl2br($ret['question_content']) . '</p>';
            $html[0] .= '</div>';
            $html[0] .= '</div>';

            if($ret['status'] == 2) {
                $html[0] .= '<div class="qa_contents_item qa_contents_even">';
                $html[0] .= '<div class="qa_contents_item_logo"><img src="' . $ret['answer_logo'] . '" onerror="onImgErr(this)"></div>';
                $html[0] .= '<div class="qa_contents_item_container qa_answer_item_container">';
                $html[0] .= '<p class="qa_contents_item_company_title">' . $ret['answer_name'] . '</p>';
                $html[0] .= '<p class="qa_contents_item_datetime qa_answer_item_datetime">回答日：' . date('Y.m.d H:i', strtotime($ret['answer_datetime'])) . '</p>';
                $html[0] .= '<p class="qa_contents_item_text">' . nl2br($ret['answer_content']) . '</p>';
                $html[0] .= '</div>';
                $html[0] .= '</div>';
            } elseif ($ret['status'] == 1) {
                $html[0] .= '<div class="qa_contents_item qa_contents_even">';
                $html[0] .= '<div class="qa_contents_item_logo"><img src="' . $ret['answer_logo'] . '" onerror="onImgErr(this)"></div>';
                $html[0] .= '<div class="qa_contents_item_container qa_answer_item_container">';
                $html[0] .= '<p class="qa_contents_item_company_title">' . $ret['answer_name'] . '</p>';
                $html[0] .= '<p class="qa_contents_item_datetime qa_answer_item_datetime">回答日：ー</p>';
                $html[0] .= '</div>';

                if ($ret['reject'] == 1) {
                    $html[0] .= '<div class="qa_contents_item_warning"><span class="qa_contents_item_warning_symbol">!</span>質問の回答をお願い致します。</div>';

                    $html[0] .= '<div class="qa_contents_item_input">';
                    $html[0] .= '<textarea class="form-control answer_content" rows="3" onkeyup="countChar(this)"></textarea>';
                    $html[0] .= '<div class="qa_contents_item_text_count"><span class="qa_contents_item_text_count_span">0</span>/2000</div>';
                    $html[0] .= '<div class="qa_contents_item_input_label">・他エージェントにも公開されるため、「会社名」「個人名」「電話番号」、その他個人情報の記載はお控えください。</div>';
                    $html[0] .= '<div class="qa_contents_button_container text-center"><a href="javascript:void(0)" data-id="' . $ret['id'] .'" data-answer_person_id="' . $answer_person_id . '" class="answerModal qa_contents_button">送信する</a></div>';
                    $html[0] .= '<div class="qa_contents_reject_container"><a href="#" data-bs-toggle="modal" data-bs-target="#rejectQuestionModal" data-id="' . $ret['id'] .'" class="rejectQuestionModal">質問への回答を拒否する</a></div>';
                    $html[0] .= '</div>';
                }

                $html[0] .= '</div>';
            }

            $html[0] .= '</div>';

            $html[0] .= '</div>';
            $html[0] .= '</div>';
            $html[0] .= '</div>';

            $data[] = $html;
        }

        $tableContent = array(
            "recordsTotal" => count($result), // total number of records
            "recordsFiltered" => count($result), // total number of records after searching, if there is no searching then totalFiltered = totalData
            "data" => $data
        );
        return $tableContent;
    }

    public function qa_list_count()
    {

        $type = $_GET['type'];
        $sort = $_GET['sort'];
        $status = $_GET['status'];
        $g_status = $status;

        $authUser = Auth::user();
        $corpoartions_id = $authUser->company_user()->first()->company_id;
        
        $questionAnswers = QuestionAnswer::where('company_id', $corpoartions_id);

        if ($type > 0) {
            $questionAnswers = $questionAnswers->where('question_type', $type);
        }

        if ($status > 0) {
            $questionAnswers = $questionAnswers->where('status', $status);
        }

        $questionAnswers = $questionAnswers->get();

        $count_question_status = [];

        $question_statuses = config('constants.question_status');

        $str_question_status[0] = '全て';
        $count_question_status[0] = 0;
        foreach ($question_statuses as $id => $status) {
            $count_question_status[$id] = 0;
            $str_question_status[$id] = $status;
        }

        foreach ($questionAnswers as $questionAnswer) {

            if (count($count_question_status) > 0) {
                $count_question_status[0]++;
                if ($questionAnswer->status > 0 && $questionAnswer->status < count($count_question_status)) {
                    $count_question_status[$questionAnswer->status]++;
                }
            }
        }

        $html = '';
        foreach ($count_question_status as $id => $count) {

            $str = isset($str_question_status[$id]) ? $str_question_status[$id] : '';
            if ($id == $g_status) {
                $html .= '<span class="qa_search_status qa_search_status_selected " data-id="' . $id . '">' . $str . '&nbsp;' . $count . '&nbsp;&nbsp;</span>';
            } else {
                $html .= '<span class="qa_search_status" data-id="' . $id . '">' . $str . '&nbsp;' . $count . '&nbsp;&nbsp;</span>';
            }
        }

        return response()->json(array('success' => true, 'html' => $html));

    }

    public function qa_answer(Request $request)
    {
        $id = $request->id;

        $questionAnswer = QuestionAnswer::find($id);

        $answer_person_id = $request->answer_person_id;
        $answer_datetime = date('Y-m-d H:i:s');
        $answer_content = $request->answer_content;

        $questionAnswer->answer_person_id = $answer_person_id;
        $questionAnswer->answer_datetime = $answer_datetime;
        $questionAnswer->answer_content = $answer_content;
        $questionAnswer->status = 2;
        $questionAnswer->save();

        $answer_content = nl2br($answer_content);
        $answer_datetime = date('Y.m.d H:i', strtotime($answer_datetime));

        if ($questionAnswer->offer_info_type == 1) {
            $questionUser = RecruitCompanyUser::with('company')->find($questionAnswer->question_person_id);
        } elseif ($questionAnswer->offer_info_type == 2) {
            $questionUser = OutsourceCompanyUser::with('company')->find($questionAnswer->question_person_id);
        }

        $question_person_id = $questionUser->user_id;

        $user = User::find($question_person_id);

        \Mail::send(new QaAnswered($user, $answer_content, $questionAnswer->question_title));

        return response()->json(array('success' => true, 'id' => $id, 'status' => 2, 'answer_content' => $answer_content, 'answer_datetime' => $answer_datetime));
    }

    function qa_reject($id)
    {
        $questionAnswer = QuestionAnswer::find($id);
        $questionAnswer->reject = 2;
        $questionAnswer->save();
        return response()->json(array('success' => true, 'id' => $id, 'reject' => 2));
    }


    private function getResultFromQuestionAnswers($questionAnswers, $count_question_status = []) {
        $result = [];
        foreach ($questionAnswers as $questionAnswer) {
            $logo = '';

            if ($questionAnswer->offer_info_type == 1) {
                $questionUser = RecruitCompanyUser::with('company')->find($questionAnswer->question_person_id);
                $offerInfo = RecruitOfferInfo::find($questionAnswer->offer_info_id);
                $job_type = 'J';
            } elseif ($questionAnswer->offer_info_type == 2) {
                $questionUser = OutsourceCompanyUser::with('company')->find($questionAnswer->question_person_id);
                $offerInfo = OutsourceOfferInfo::find($questionAnswer->offer_info_id);
                $job_type = 'G';
            }
            
            $question_name = $questionUser->company->name;

            if ($questionUser->company->logo == '') {
                $question_logo = config('constants.no_image_url');
            } else {
                //$question_logo = '/storage/' . $questionUser->company->id . '/logo/' . $questionUser->company->logo;
                $question_logo = '/storage/' . $questionUser->company->id . '/' . $questionUser->company->logo;
            }



            $answerCompanyUser = CompanyUser::with('company')->find($questionAnswer->answer_person_id);

            $answer_name = $answerCompanyUser->company->name;
            
            //if ($answerCompanyUser->company->logo == config('constants.no_image_url')) {
            if ($answerCompanyUser->company->logo == '') {
                //$answer_logo = $answerCompanyUser->company->logo;
                $answer_logo = config('constants.no_image_url');
            } else {
                //$answer_logo = '/storage/' . $answerCompanyUser->company_id . '/logo/' . $answerCompanyUser->company->logo;
                //$answer_logo = '/storage/' . $answerCompanyUser->company_id . '/' . $answerCompanyUser->company->logo;
                $answer_logo = $answerCompanyUser->company->logo;
            }

            $ret = array(
                'id' => $questionAnswer->id,

                'job_id' => $offerInfo->id,
                'job_type' => $job_type,

                'job_title' => $offerInfo->job_title,
                'job_image' => '/storage/' . $offerInfo->company_id . '/' . $offerInfo->image_main,
                'offer_info_type' => $questionAnswer->offer_info_type,
                'offer_info_type_name' => $questionAnswer->offer_info_type_name,

                'question_name' => $question_name,
                'question_logo' => $question_logo,
                'question_title' => $questionAnswer->question_title,
                'question_content' => $questionAnswer->question_content,

                'answer_name' => $answer_name,
                'answer_logo' => $answer_logo,
                'answer_content' => $questionAnswer->answer_content,

                'question_type' => $questionAnswer->question_type,
                'question_type_name' => $questionAnswer->question_type_name,


                'status' => $questionAnswer->status,
                'status_name' => $questionAnswer->status_name,

                'reject' => $questionAnswer->reject,
                'question_datetime' => $questionAnswer->question_datetime,
                'answer_datetime' => $questionAnswer->answer_datetime,

                'ago' => $questionAnswer->ago,


            );
            
            if (count($count_question_status) > 0) {
                $count_question_status[0]++;
                if ($questionAnswer->status > 0 && $questionAnswer->status < count($count_question_status)) {
                    $count_question_status[$questionAnswer->status]++;
                }
            }
            $result[] = $ret;
        }
        
        return array('list' => $result, 'count' => $count_question_status);
    }


}

