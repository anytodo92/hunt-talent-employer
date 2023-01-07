@if($user->is_company)
{{ $user->company_user->company->name }}<br>
{{ $user->company_user->name }}様<br>
@elseif($user->is_admin)
管理者様<br>
@endif
インクル求人システムのパスワードの再設定を以下よりお願いします。<br><br>

パスワードの再設定URL： <a href="{{ $reset_url }}">{{ $reset_url }}</a>