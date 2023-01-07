<div class="modal fade" id="recruitPublicModal1" tabindex="-1" aria-labelledby="recruitPublicModal1Label" aria-hidden="true" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">

                <h5 class="modal-title" id="recruitPublicModal1Label"><img src="/assets/static/images/logo.png" alt="">求人公開申請</h5>
                {{--                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>--}}
            </div>
            <div class="modal-body">
                <p>内容をご確認の上、申請をお願い致します。</p>
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 30%">求人タイトル</th>
                        <td>asdfsadfasf</td>
                    </tr>
                    <tr>
                        <th>職種</th>
                        <td>〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇</td>
                    </tr>
                </table>

                <table class="table table-bordered">
                    <tr>
                        <th style="width: 30%">成功報酬支払い方法</th>
                        <td>割合（％）</td>
                    </tr>
                    <tr>
                        <th>報酬</th>
                        <td>理論年収の25%</td>
                    </tr>
                    <tr>
                        <th>理論年収の定義</th>
                        <td>〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇</td>
                    </tr>
                    <tr>
                        <th>返金規定</th>
                        <td>〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇〇</td>
                    </tr>
                </table>

                <div class="modal_notification">
                    報酬を変更する場合は、こちらの公開申請をキャンセルして「成功報酬額の変更申請ボタン」から申請をお願い致します。<br>
                    上記の成功報酬以外に、採用（入社）時には採用事務手数料が発生いたします。<br>
                    採用事務手数料　成功報酬額の20%（上限15万円）
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>
                <input type="hidden" id="publicRecruitId" />
                <input type="hidden" id="publicType" />
                <button type="button" class="btn btn-primary" onclick="publicFunc();">公開する</button>
            </div>
        </div>
    </div>
</div>
