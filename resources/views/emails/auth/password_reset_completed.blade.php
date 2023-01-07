@if($user->is_company)
{{ $user->company_user->company->name }}<br>
{{ $user->company_user->name }}様<br>
@elseif($user->is_admin)
管理者様<br>
@endif
インクル求人システムへのパスワード再設定が完了しました。<br><br>

ログインURL:
@if($user->is_company)
    <a href="{{ route('login') }}">{{ route('login') }}</a>
@elseif($user->is_admin)
    <a href="{{ route('admin.login') }}">{{ route('admin.login') }}</a>
@endif