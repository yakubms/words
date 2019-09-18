        <nav class="navbar is-light" role="navigation" aria-label="main navigation">
            <div class="navbar-brand">
                @auth
                <router-link class="navbar-item" to="/">Tango</router-link>
                @else
                <a class="navbar-item" href="/">Tango</a>
                @endauth
                <a role="button" :class="burgerClass" aria-label="menu" aria-expanded="false" @click="toggleBurger">
                    <span aria-hidden="true">
                    </span>
                    <span aria-hidden="true">
                    </span>
                    <span aria-hidden="true">
                    </span>
                </a>
            </div>
            <div id="navbar" :class="navbarClass" @click="toggleBurger">
                <div class="navbar-start">
                    @if(!request()->is('register') and !request()->is('login') and !request()->is('password*'))
                    <router-link class="navbar-item" to="/about">このサイトについて
                    </router-link>
                    <router-link class="navbar-item" to="/exam">テストを受ける
                    </router-link>
                    @endif
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
                    <a class="navbar-item" href="{{ route('logout') }}" @click.prevent="onLogout">
                        {{ __('ログアウト') }}
                    </a>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                        @csrf
                    </form>
                    @else
                    <div class="navbar-item">
                        <div class="buttons">
                            <a class="button is-success" href="{{ route('register') }}">
                                <strong>新規登録
                                </strong>
                            </a>
                            <a class="button is-info" href="{{ route('login') }}">
                                ログイン
                            </a>
                        </div>
                    </div>
                    @endauth
                </div>
            </div>
        </nav>
