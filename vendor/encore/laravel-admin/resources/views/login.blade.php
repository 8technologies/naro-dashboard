<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <title>{{ config('admin.title') }} | {{ trans('admin.login') }}</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">

  {{-- Favicon --}}
  @if(!is_null($favicon = Admin::favicon()))
    <link rel="shortcut icon" href="{{ $favicon }}">
  @endif

  {{-- Google Font --}}
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  {{-- Bootstrap & FontAwesome & AdminLTE CSS --}}
  <link rel="stylesheet" href="{{ admin_asset('vendor/laravel-admin/AdminLTE/bootstrap/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ admin_asset('vendor/laravel-admin/font-awesome/css/font-awesome.min.css') }}">
  <link rel="stylesheet" href="{{ admin_asset('vendor/laravel-admin/AdminLTE/dist/css/AdminLTE.min.css') }}">

  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background: url({{ asset('assets/images/gnut.jpg') }}) no-repeat center center fixed;
      background-size: cover;
    }
    .navbar-transparent {
      background-color: rgba(0,0,0,0.5) !important;
      border: none;
    }
    .navbar-brand {
      font-weight: 600;
      font-size: 20px;
      color: #fff !important;
    }
    .login-container {
      display: flex;
      align-items: center;
      justify-content: center;
      min-height: 90vh;
    }
    .login-card {
      background: rgba(255,255,255,0.9);
      border-radius: 12px;
      box-shadow: 0 8px 30px rgba(0,0,0,0.2);
      max-width: 400px;
      width: 100%;
      padding: 2rem;
    }
    .login-logo img {
      display: block;
      margin: 0 auto 1rem;
      width: 80px;
      height: 80px;
      border-radius: 50%;
      box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    }
    .form-control {
      border-radius: 8px;
      padding: 0.75rem 1rem;
    }
    .btn-login {
      background: linear-gradient(90deg, #2a7f62 0%, #1e5c44 100%);
      border: none;
      border-radius: 8px;
      padding: 0.75rem;
      font-weight: 600;
    }
    .btn-login:hover {
      background: linear-gradient(90deg, #1e5c44 0%, #2a7f62 100%);
    }
    .text-error {
      color: #e74c3c;
      font-size: 0.9rem;
    }
    .icheckbox_square-blue, .iradio_square-blue {
      margin-top: 0;
    }
  </style>
</head>
<body>

<nav class="navbar navbar-default navbar-transparent">
  <div class="container">
    <div class="navbar-header">
      <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#nav">
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
        <span class="icon-bar"></span>
      </button>
      <a class="navbar-brand" href="{{ url('/') }}">NARO GROUNDNUTS</a>
    </div>
   
  </div>
</nav>

<div class="login-container">
  <div class="login-card">
    <div class="login-logo">
      <a href="{{ admin_url('/') }}">
        <img src="{{ asset('assets/images/logo.jpeg') }}" alt="Logo">
      </a>
    </div>
    @if(Session::has('success'))
      <div class="alert alert-success">{{ Session::get('success') }}</div>
    @endif
    @if(Session::has('error'))
      <div class="alert alert-danger">{{ Session::get('error') }}</div>
    @endif

    <form action="{{ admin_url('auth/login') }}" method="post">
      @csrf
      <div class="form-group {{ $errors->has('username') ? 'has-error' : '' }}">
        <input type="text" name="username" value="{{ old('username') }}"
               class="form-control" placeholder="{{ trans('admin.username') }}">
        @if($errors->has('username'))
          <span class="text-error">{{ $errors->first('username') }}</span>
        @endif
      </div>

      <div class="form-group {{ $errors->has('password') ? 'has-error' : '' }}">
        <input type="password" name="password"
               class="form-control" placeholder="{{ trans('admin.password') }}">
        @if($errors->has('password'))
          <span class="text-error">{{ $errors->first('password') }}</span>
        @endif
      </div>

      <div class="row">
        <div class="col-xs-6">
          @if(config('admin.auth.remember'))
            <div class="checkbox icheck">
              <label>
                <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}> {{ trans('admin.remember_me') }}
              </label>
            </div>
          @endif
        </div>
        <div class="col-xs-6 text-right">
          <button type="submit" class="btn btn-login btn-block">{{ trans('admin.login') }}</button>
        </div>
      </div>
    </form>
  </div>
</div>

<script src="{{ admin_asset('vendor/laravel-admin/AdminLTE/plugins/jQuery/jQuery-2.1.4.min.js') }}"></script>
<script src="{{ admin_asset('vendor/laravel-admin/AdminLTE/bootstrap/js/bootstrap.min.js') }}"></script>
<script src="{{ admin_asset('vendor/laravel-admin/AdminLTE/plugins/iCheck/icheck.min.js') }}"></script>
<script>
  $(function () {
    $('input').iCheck({
      checkboxClass: 'icheckbox_square-blue',
      radioClass: 'iradio_square-blue',
      increaseArea: '20%'
    });
  });
</script>
</body>
</html>