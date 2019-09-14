<!doctype html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tango
    </title>
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app">
      <nav class="navbar" role="navigation" aria-label="main navigation">
        <div class="navbar-brand">
            <a role="button" class="navbar-burger burger" aria-label="menu" aria-expanded="false" data-target="navbarBasicExample">
                <span aria-hidden="true">
                </span>
                <span aria-hidden="true">
                </span>
                <span aria-hidden="true">
                </span>
            </a>
        </div>
        <div id="navbarBasicExample" class="navbar-menu">
          <div class="navbar-start">
            <router-link class="navbar-item" to="/about">このサイトについて
            </router-link>

            <router-link class="navbar-item" to="/exam">テストを受ける
            </router-link>
            @auth
            <router-link class="navbar-item" to="/search">単語検索
            </router-link>
            <router-link class="navbar-item" to="/study">復習
            </router-link>
            <router-link class="navbar-item" to="/words">単語帳
            </router-link>
            @endauth
        </div>
        <div class="navbar-end">
          @auth
            <router-link class="navbar-item" to="/">ホーム
            </router-link>
          <a class="navbar-item" href="{{ route('logout') }}"
          onclick="event.preventDefault();
          document.getElementById('logout-form').submit();">
          {{ __('ログアウト') }}
      </a>
      <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
    @else
    <div class="navbar-item">
        <div class="buttons">
          <a class="button is-primary" href="{{ route('register') }}">
            <strong>新規登録
            </strong>
        </a>
        <a class="button is-light" href="{{ route('login') }}">
            ログイン
        </a>
    </div>
</div>
@endauth
</div>
</div>
</nav>
<div>
    <router-view>
    </router-view>
</div>
</div>
<script>
    window.Laravel = {!! json_encode([
        'apiToken' => \Auth::user()->api_token ?? null
    ]) !!};
</script>
<script src="{{ mix('js/app.js') }}">
</script>
</body>
</html>
