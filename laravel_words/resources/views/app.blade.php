<!doctype html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>Tango</title>
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    </head>
    <body>
            @if (Route::has('login'))
                <div class="top-right links">
                    @auth
                        <a href="{{ url('/home') }}">Home</a>
 <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault();
                                                     document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>

                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                                        @csrf
                                    </form>
                    @else
                        <a href="{{ route('login') }}">Login</a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}">Register</a>
                        @endif
                    @endauth
                </div>
            @endif

        <div id="app">
            <main class="flex">
                <aside class="w-1/5">
                    <section class="mb-8">
                        <h5 class="uppercase font-bold mb-4">メニュー</h5>

                        <ul class="list-reset">
                        <li class="text-sm pb-4"><router-link to="/" exact>ホーム</router-link></li>
                        <li class="text-sm pb-4"><router-link to="about">このサイトについて</router-link></li>
                        <li class="text-sm pb-4"><router-link to="exam">テストを受ける</router-link></li>
                    @auth
                        <li class="text-sm pb-4"><router-link to="study">復習する</router-link></li>
                        <li class="text-sm pb-4"><router-link to="words">単語</router-link></li>
                    @endauth
                        </ul>
                    </section>
                </aside>

                <div class="primary flex-1">
                    <router-view></router-view>
                </div>
            </main>
        </div>
        <script src="{{ mix('js/app.js') }}"></script>
    </body>
</html>
