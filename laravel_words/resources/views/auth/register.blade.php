@extends('layouts.default')

@section('content')
     @include('layouts.nav')

    <div class="card">
        <header class="card-header">
            <p class="card-header-title">新規ユーザ登録</p>
        </header>
   <div class="card-content">
        <div class="content">
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <div class="field">
                    <div class="control">
                        <label for="name">{{ __('ハンドル名（任意）') }}</label>
                        <input id="name" type="text" class="input @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" autocomplete="name" autofocus placeholder="空欄の場合はユーザIDがハンドル名になります">

                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="field">
                    <label for="userid" class="col-md-4 col-form-label text-md-right">{{ __('ユーザID') }}</label>

                    <div class="control">
                        <input id="userid" type="text" class="input @error('userid') is-invalid @enderror" name="userid" value="{{ old('userid') }}" required autocomplete="userid" autofocus placeholder="3～12文字の英数字">

                        @error('userid')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="field">
                    <label for="email" class="col-md-4 col-form-label text-md-right">{{ __('メールアドレス') }}</label>

                    <div class="control">
                        <input id="email" type="email" class="input @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="パスワード再発行用のメールアドレス">

                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="field">
                    <label for="password" class="col-md-4 col-form-label text-md-right">{{ __('パスワード') }}</label>

                    <div class="control">
                        <input id="password" type="password" class="input @error('password') is-invalid @enderror" name="password" required autocomplete="new-password" placeholder="英数字または記号">

                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="field">
                    <label for="password-confirm" class="col-md-4 col-form-label text-md-right">{{ __('パスワード（確認）') }}</label>

                    <div class="control">
                        <input id="password-confirm" type="password" class="input" name="password_confirmation" required autocomplete="new-password" placeholder="パスワード（確認用）">
                    </div>
                </div>

                <div class="field">
                    <div class="control">
                        <button type="submit" class="button is-primary">
                            {{ __('登録') }}
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    </div>
@endsection
