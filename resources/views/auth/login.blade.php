<!DOCTYPE html>
<html>

<head>

<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<title>{{ $setting->nama_perusahaan }} | Login</title>

<link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
<link href="{{ asset('font-awesome/css/font-awesome.css') }}" rel="stylesheet">

<link href="{{ asset('css/animate.css') }}" rel="stylesheet">
<link href="{{ asset('css/style.css') }}" rel="stylesheet">

</head>


<body class="gray-bg">


<div class="middle-box text-center loginscreen animated fadeInDown">

<div>

<div>

@if($setting->path_logo)

<img src="{{ asset($setting->path_logo) }}" width="120">

@else

<h1 class="logo-name">IN+</h1>

@endif


</div>


<h3>{{ $setting->nama_perusahaan }}</h3>

<p>

{{ $setting->alamat }}  
<br>
{{ $setting->telepon }}

</p>


@if(session('error'))

<div class="alert alert-danger">

{{ session('error') }}

</div>

@endif



<form class="m-t" method="POST" action="{{ route('authenticate') }}">

@csrf


<div class="form-group">

<input type="text" class="form-control" name="username" placeholder="Username" required>

</div>


<div class="form-group">

<input type="password" class="form-control" name="password" placeholder="Password" required>

</div>


<button type="submit" class="btn btn-primary block full-width m-b">

Login

</button>


</form>


<p class="m-t">

<small>

{{ $setting->nama_perusahaan }}

</small>

</p>


</div>

</div>


<script src="{{ asset('js/jquery-3.1.1.min.js') }}"></script>

<script src="{{ asset('js/popper.min.js') }}"></script>

<script src="{{ asset('js/bootstrap.js') }}"></script>

</body>

</html>