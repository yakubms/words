@extends('layouts.default')

@section('content')
     @include('layouts.nav')
<div class="card">
    <header class="card-header">
        <p class="card-header-title">ログイン</p>
    </header>

   <div class="card-content">
        <div class="content">
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="field">
                    <label for="userid">{{ __('ユーザーID') }}</label>

                    <div class="control">
                        <input id="userid" type="text" class="input @error('userid') is-invalid @enderror" name="userid" value="{{ old('userid') }}" required autocomplete="userid" autofocus>

                        @error('userid')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="field">
                    <label for="password">{{ __('パスワード') }}</label>

                    <div class="control">
                        <input id="password" type="password" class="input @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>
                </div>

                <div class="field">
                    <div class="control">
                        <input class="checkbox" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>

                        <label class="checkbox" for="remember">
                            {{ __('ログインを記憶する') }}
                        </label>
                    </div>
                </div>

                <div class="field">
                    <div class="control">
                        <button type="submit" class="button is-primary">
                            {{ __('ログイン') }}
                        </button>

{{--                         @if (Route::has('password.request'))
                            <a class="button is-link" href="{{ route('password.request') }}">
                                {{ __('パスワードを忘れた') }}
                            </a>
                        @endif --}}
                    </div>
                </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
