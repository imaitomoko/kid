<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>KID</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+JP&display=swap" rel="stylesheet">

    @yield('css')
</head>

<body>
    <header class="header">
        <div class="header__inner">
            <a class="header__logo" href="/">
                <img src="{{ asset('images/turtle.png') }}" alt="Turtle" class="logo-turtle">
                <div class="logo-text-group">
                    <img src="{{ asset('images/logo.png') }}" alt="Logo" class="logo-image">
                    <span class="logo-kid">K I D</span>
                </div>
            </a>
            @auth
            <form action="/logout" method="POST">
                @csrf
                <button class="header-nav__button" type="submit">ログアウト</button>
            </form>
            @endauth
        </div>
    </header>

    <main>
    @yield('content')
    </main>
    
    <footer class="footer">
        &copy; とうばんの森こども園. All rights reserved.
    </footer>
</body>

</html>