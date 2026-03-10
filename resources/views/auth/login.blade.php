<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>{{ $setting->nama_perusahaan }} | Login</title>

    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    
    <!-- Font Awesome - Gunakan CDN untuk memastikan -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    
    <!-- Fallback jika CDN gagal -->
    <link href="{{ asset('font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">

    <link href="{{ asset('css/animate.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #1c84c6 0%, #23c6c8 100%);
        }
        .middle-box {
            max-width: 400px;
            padding: 20px 20px 30px 20px;
            z-index: 100;
        }
        .logo-name {
            background: white;
            border-radius: 50%;
            width: 100px;
            height: 100px;
            margin: 0 auto 20px auto;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            font-size: 48px;
            color: #1c84c6;
        }
        .login-panel {
            background: rgba(255, 255, 255, 0.95);
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        .company-info {
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e7eaec;
        }
        .company-info h3 {
            color: #1c84c6;
            font-weight: 600;
            margin-bottom: 5px;
        }
        .company-info p {
            color: #666;
            font-size: 13px;
            margin-bottom: 0;
        }
        .company-info i {
            color: #23c6c8;
            margin-right: 5px;
            width: 20px;
        }
        
        /* Input group styling */
        .input-group {
            position: relative;
            display: flex;
            width: 100%;
            border-collapse: separate;
        }
        
        .input-group-addon {
            padding: 10px 12px;
            font-size: 14px;
            font-weight: 400;
            line-height: 1;
            color: #555;
            text-align: center;
            background-color: #f3f3f4;
            border: 1px solid #e5e6e7;
            border-radius: 4px 0 0 4px;
            border-right: none;
            width: 45px;
            white-space: nowrap;
            vertical-align: middle;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .input-group .form-control {
            position: relative;
            flex: 1 1 auto;
            width: 1%;
            margin-bottom: 0;
            height: 45px;
            border-left: none;
            border-radius: 0 4px 4px 0;
        }
        
        .form-control {
            display: block;
            width: 100%;
            padding: 10px 12px;
            font-size: 14px;
            line-height: 1.42857;
            color: #555;
            background-color: #fff;
            background-image: none;
            border: 1px solid #e5e6e7;
            border-radius: 4px;
            transition: border-color ease-in-out .15s,box-shadow ease-in-out .15s;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .input-group:hover .input-group-addon {
            background: #e7eaec;
            color: #1c84c6;
            transition: all 0.3s;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #1c84c6 0%, #23c6c8 100%);
            border: none;
            height: 45px;
            font-size: 16px;
            font-weight: 500;
            letter-spacing: 1px;
            border-radius: 5px;
            box-shadow: 0 5px 10px rgba(28, 132, 198, 0.3);
            transition: all 0.3s;
            color: white;
            width: 100%;
            cursor: pointer;
        }
        
        .btn-login:hover {
            background: linear-gradient(135deg, #1a78b2 0%, #1fb2b4 100%);
            transform: translateY(-2px);
            box-shadow: 0 8px 15px rgba(28, 132, 198, 0.4);
        }
        
        .btn-login i {
            margin-right: 8px;
        }
        
        .alert {
            border-radius: 5px;
            padding: 12px 15px;
            border: none;
            background: #f8d7da;
            color: #721c24;
            margin-bottom: 25px;
            font-size: 14px;
            text-align: left;
        }
        
        .alert i {
            margin-right: 5px;
            color: #dc3545;
        }
        
        .footer-text {
            margin-top: 25px;
            color: rgba(255,255,255,0.9);
            font-size: 12px;
            text-align: center;
        }
        
        .footer-text small {
            background: rgba(0,0,0,0.1);
            padding: 5px 15px;
            border-radius: 20px;
            display: inline-block;
        }
        
        .form-check {
            text-align: left;
            margin-top: 10px;
            margin-bottom: 15px;
        }
        
        .form-check label {
            color: #666;
            font-size: 13px;
            cursor: pointer;
        }
        
        .form-check-input {
            margin-right: 5px;
        }
        
        .animated {
            animation-duration: 0.5s;
        }
        
        /* Fallback jika font-awesome tidak muncul */
        .fa-user:before {
            content: "👤";
            font-family: Arial, sans-serif;
        }
        
        .fa-lock:before {
            content: "🔒";
            font-family: Arial, sans-serif;
        }
        
        .fa-map-marker:before {
            content: "📍";
            font-family: Arial, sans-serif;
        }
        
        .fa-phone:before {
            content: "📞";
            font-family: Arial, sans-serif;
        }
        
        .fa-exclamation-circle:before {
            content: "⚠️";
            font-family: Arial, sans-serif;
        }
        
        .fa-sign-in:before {
            content: "→";
            font-family: Arial, sans-serif;
        }
    </style>
</head>

<body class="gray-bg">
    <div class="middle-box text-center animated fadeInDown">
        <div class="login-panel">
            <!-- Logo -->
            <div>
                @if($setting->path_logo)
                    <img src="{{ asset($setting->path_logo) }}" alt="Logo" style="max-width: 120px; margin-bottom: 20px; border-radius: 10px;">
                @else
                    <h1 class="logo-name">IN+</h1>
                @endif
            </div>

            <!-- Company Info -->
            <div class="company-info">
                <h3>{{ $setting->nama_perusahaan }}</h3>
                <p>
                     {{ $setting->alamat }}
                    
                </p>
            </div>

            <!-- Error Alert -->
            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fa fa-exclamation-circle"></i> 
                    {{ session('error') }}
                </div>
            @endif

            <!-- Login Form -->
            <form class="m-t" method="POST" action="{{ route('authenticate') }}">
                @csrf

                <!-- Username Field -->
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-user"></i>
                        </span>
                        <input type="text" 
                               class="form-control" 
                               name="username" 
                               placeholder="Username" 
                               required 
                               autofocus>
                    </div>
                </div>

                <!-- Password Field -->
                <div class="form-group">
                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="fa fa-lock"></i>
                        </span>
                        <input type="password" 
                               class="form-control" 
                               name="password" 
                               placeholder="Password" 
                               required>
                    </div>
                </div>

                <!-- Login Button -->
                <button type="submit" class="btn-login">  
                    LOGIN
                </button>
            </form>
        </div>

        <!-- Footer -->
        <p class="footer-text">
            <small>
                &copy; {{ date('Y') }} {{ $setting->nama_perusahaan }}. All rights reserved.
            </small>
        </p>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('js/jquery-3.1.1.min.js') }}"></script>
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.js') }}"></script>
    
    <script>
        $(document).ready(function() {
            // Auto-hide alert
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
            
            // Check if font-awesome loaded
            var faTest = $('<i class="fa fa-user"></i>').appendTo('body');
            if (faTest.css('fontFamily') !== 'FontAwesome') {
                console.log('Font Awesome not loaded, using fallback');
            }
            faTest.remove();
        });
    </script>
</body>

</html>