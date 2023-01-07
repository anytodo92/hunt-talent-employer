<div class="modal fade" id="outsourcePublicModal" tabindex="-1" aria-labelledby="outsourcePublicModalLabel" aria-hidden="true" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="outsourcePublicModallabel"><img src="/assets/static/images/logo.png" alt="">業務委託案件の公開申請</h5>
            </div>
            <div class="modal-body">
                <p>内容をご確認の上、申請をお願い致します。</p>
                <table class="table table-bordered">
                    <tr>
                        <th style="width: 30%">求人タイトル</th>
                        <td id="outsource-modal-title"></td>
                    </tr>
                    <tr>
                        <th>報酬単価</th>
                        <td id="outsource-modal-unit_price"></td>
                    </tr>
                </table>

                <div class="modal_notification">
                    参画後の報酬単価は、当社へお支払いいただき、当社から業務委託/SES企業様へお支払いする流れとなります（参画が終了するまでこの流れが継続します）
                    <p class="text-error">※業務委託/SES企業様との直接契約は規約違反となり、罰則の対象となりますのでご注意ください。</p>
                    <p>参画者への報酬単価以外に当社手数料および採用報酬などが発生することはございません。募集から採用まで無料でご利用いただけます。</p>
                </div>

                <div class="modal_footer_check text-center">
                    <input type="checkbox" onchange="agreeOutsourcePublicModal(this);" id="agreeOutsourcePublicLabel">
                    <label for="agreeOutsourcePublicLabel">上記内容を確認しました。</label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">キャンセル</button>

                <input type="hidden" id="publicId" />
                <input type="hidden" id="publicType" />

                <button type="button" class="btn btn-primary" onclick="publicFunc();" disabled id="publicOutsourceBtn">公開申請</button>
            </div>
        </div>
    </div>
</div>
