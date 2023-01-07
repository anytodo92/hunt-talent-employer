{{ $company_user->company->name }}<br>
{{ $company_user->name }}様<br>
インクル求人システムへのお問い合わせ/資料請求が完了しました。

@if($with_document === true)
    <br><br>
    申し込み資料のURL: <a href="https://agent.incul.jp/applyform.pdf">https://agent.incul.jp/applyform.pdf</a>
@endif