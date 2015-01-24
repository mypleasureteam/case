<!DOCTYPE html>
<html lang="en">

  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>mypleasu.re</title>

    {{ HTML::style('css/bootstrap.min.css') }}
    {{ HTML::style('css/bootstrap-theme.min.css') }}

    <!--[if lt IE 9]>
      <script src="//oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="//oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>
    <nav class="navbar navbar-inverse">
      <header class="navbar-header">
        <a class="navbar-brand" href="">mypleasu.re</a>
      </header>
      <ul class="nav navbar-nav">
        @if (Auth::check())
          <li><a href="{{{ URL::route('user.profile') }}}">me</a></li>
          <li><a href="{{{ URL::route('user.videos') }}}">my videos</a></li>
          <li><a href="{{{ URL::route('user.edit.email') }}}">edit email</a></li>
          <li><a href="{{{ URL::route('user.edit.password') }}}">change password</a></li>
          <li><a href="{{{ URL::route('auth.logout') }}}">log out</a></li>
        @else
          <li><a href="{{{ URL::route('auth.register') }}}">register</a></li>
          <li><a href="{{{ URL::route('auth.login') }}}">sign in</a></li>
        @endif
      </ul>
    </nav>

    <section class="container">
      @yield('content', 'mypleasu.re')
    </section>

    {{ HTML::script('js/jquery.min.js') }}
    {{ HTML::script('js/bootstrap.min.js') }}
  </body>

</html>
