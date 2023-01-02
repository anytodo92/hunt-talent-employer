<?php

namespace Database\Seeders;

use App\Models\FirstIndustry;
use App\Models\SecondIndustry;
use Illuminate\Database\Seeder;

class IndustriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $first_industries = [
            1 => 'IT',
            2 => 'メーカー',
            3 => '商社',
            4 => '小売・物流・運輸 ',
            5 => '金融',
            6 => '建築・不動産',
            7 => '人材・教育',
            8 => 'サービス',
            9 => 'コンサルティング・調査',
            10 => '広告・出版・マスコミ',
            11 => 'インフラ・官公庁',
        ];

        $second_industries = collect([
            ['first_industry_id' => 1, 'name' => 'ソフトウェア 情報処理'],
            ['first_industry_id' => 1, 'name' => 'インターネット関連 ゲーム'],
            ['first_industry_id' => 1, 'name' => '通信'],
            ['first_industry_id' => 2, 'name' => '自動車・輸送機器'],
            ['first_industry_id' => 2, 'name' => '電子・電子部品・半導体'],
            ['first_industry_id' => 2, 'name' => '機械関連'],
            ['first_industry_id' => 2, 'name' => '医療機器'],
            ['first_industry_id' => 2, 'name' => '食料品'],
            ['first_industry_id' => 2, 'name' => '医薬品・化粧品・生活用品'],
            ['first_industry_id' => 2, 'name' => '化学・繊維・素材'],
            ['first_industry_id' => 2, 'name' => 'ファッション・アパレル'],
            ['first_industry_id' => 2, 'name' => 'その他'],
            ['first_industry_id' => 3, 'name' => '総合商社'],
            ['first_industry_id' => 3, 'name' => '専門商社'],
            ['first_industry_id' => 3, 'name' => '商社（その他）'],
            ['first_industry_id' => 4, 'name' => '物流 運輸 倉庫'],
            ['first_industry_id' => 4, 'name' => 'ファッション アパレル'],
            ['first_industry_id' => 4, 'name' => 'スーパー CVS'],
            ['first_industry_id' => 4, 'name' => '専門店'],
            ['first_industry_id' => 4, 'name' => 'その他'],
            ['first_industry_id' => 5, 'name' => '銀行'],
            ['first_industry_id' => 5, 'name' => '生命保険 損害保険'],
            ['first_industry_id' => 5, 'name' => '証券 投資関連'],
            ['first_industry_id' => 5, 'name' => '金融 保険（その他）'],
            ['first_industry_id' => 6, 'name' => '建築・土木・設計'],
            ['first_industry_id' => 6, 'name' => '不動産'],
            ['first_industry_id' => 6, 'name' => '不動産・建設系（その他）'],
            ['first_industry_id' => 7, 'name' => '人材サービス'],
            ['first_industry_id' => 7, 'name' => '教育関連'],
            ['first_industry_id' => 8, 'name' => 'フード・レストラン'],
            ['first_industry_id' => 8, 'name' => 'レジャー・ホテル・旅行'],
            ['first_industry_id' => 8, 'name' => 'エンターテイメント・スポーツ'],
            ['first_industry_id' => 8, 'name' => '医療'],
            ['first_industry_id' => 8, 'name' => '福祉・保育'],
            ['first_industry_id' => 8, 'name' => '冠婚葬祭'],
            ['first_industry_id' => 8, 'name' => 'サービス（その他）'],
            ['first_industry_id' => 9, 'name' => 'シンクタンク・コンサルティングファーム'],
            ['first_industry_id' => 9, 'name' => '監査法人・税理士事務所・法律事務所'],
            ['first_industry_id' => 9, 'name' => '専門コンサル（その他）'],
            ['first_industry_id' => 10, 'name' => '広告'],
            ['first_industry_id' => 10, 'name' => 'イベント PR'],
            ['first_industry_id' => 10, 'name' => '放送 映像 音響'],
            ['first_industry_id' => 10, 'name' => '印刷 出版'],
            ['first_industry_id' => 10, 'name' => 'マスコミ（その他）'],
            ['first_industry_id' => 11, 'name' => '鉄道・航空'],
            ['first_industry_id' => 11, 'name' => '団体・連合会・官公庁'],
            ['first_industry_id' => 11, 'name' => '電気・ガス・水道・エネルギー・環境関連'],
            ['first_industry_id' => 11, 'name' => 'その他の業種'],
        ]);

        $grouped_second_industries = $second_industries->groupBy('first_industry_id');

        foreach ($grouped_second_industries as $first_industry_id => $second_industries) {

            $first_industry = new FirstIndustry();
            $first_industry->name = $first_industries[$first_industry_id];
            $first_industry->save();

            foreach($second_industries as $target_second_industry) {

                $second_industry = new SecondIndustry();
                $second_industry->first_industry_id = $first_industry_id;
                $second_industry->name = $target_second_industry['name'];
                $second_industry->save();

            }

        }

    }
}