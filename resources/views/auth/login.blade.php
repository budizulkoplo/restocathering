<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $appName = $setting->name ?? $setting->nama_perusahaan ?? 'Resto Catering';
        $appAddress = $setting->address ?? $setting->alamat ?? '-';
        $appLogo = $setting->logo_path ?? $setting->path_logo ?? null;
    @endphp
    <title>{{ $appName }} | Login</title>

    <link href="{{ asset('css/bootstrap.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="{{ asset('font-awesome/css/font-awesome.min.css') }}" rel="stylesheet">
    <link href="{{ asset('css/animate.css') }}" rel="stylesheet">
    <link href="{{ asset('css/style.css') }}" rel="stylesheet">

    <style>
        body {
            margin: 0;
            min-height: 100vh;
            color: #fff;
            overflow-x: hidden;
            position: relative;
        }

        body::before{
            content:"";
            position: fixed;
            inset: 0;
            z-index: -1;

            background:
                radial-gradient(circle at top right, rgba(35,198,200,0.32), transparent 28%),
                linear-gradient(140deg,#0b4f7a 0%,#0f6ea4 50%,#22b8b0 100%);
        }

        .public-shell {
            min-height: 100vh;
            padding: 26px;
        }

        .calendar-screen {
            min-height: calc(100vh - 52px);
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.16);
            border-radius: 28px;
            backdrop-filter: blur(14px);
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.18);
            padding: 26px;
        }

        .screen-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 22px;
        }

        .screen-title {
            max-width: 720px;
        }

        .screen-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.16);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: .7px;
            text-transform: uppercase;
            margin-bottom: 14px;
        }

        .screen-title h1 {
            margin: 0 0 10px;
            font-size: 38px;
            line-height: 1.05;
            font-weight: 800;
            color: #fff;
        }

        .screen-title p {
            margin: 0;
            font-size: 15px;
            line-height: 1.6;
            color: rgba(255, 255, 255, 0.85);
        }

        .screen-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .month-nav {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.14);
        }

        .month-nav-btn {
            width: 38px;
            height: 38px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            text-decoration: none;
            background: rgba(255, 255, 255, 0.12);
        }

        .month-label {
            min-width: 150px;
            text-align: center;
            font-size: 14px;
            font-weight: 700;
            color: #fff;
        }

        .login-trigger {
            border: 0;
            border-radius: 999px;
            background: #fff;
            color: #0f6ea4;
            font-weight: 700;
            font-size: 13px;
            padding: 12px 18px;
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
        }

        .calendar-toolbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 18px;
            margin-bottom: 18px;
        }

        .calendar-legend {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            color: rgba(255, 255, 255, 0.86);
            font-size: 12px;
        }

        .calendar-legend span {
            display: inline-flex;
            align-items: center;
            gap: 7px;
        }

        .calendar-summary {
            padding: 10px 14px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.12);
            font-size: 13px;
            color: rgba(255, 255, 255, 0.88);
        }

        .public-calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 12px;
        }

        .public-weekday,
        .public-calendar-day {
            border-radius: 18px;
        }

        .public-weekday {
            text-align: center;
            padding: 12px 8px;
            background: rgba(255, 255, 255, 0.12);
            color: rgba(255, 255, 255, 0.92);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 12px;
            letter-spacing: .6px;
        }

        .public-calendar-day {
            min-height: 170px;
            background: rgba(255, 255, 255, 0.95);
            color: #2f4050;
            border: 1px solid rgba(11, 79, 122, 0.08);
            padding: 12px;
            box-shadow: 0 14px 24px rgba(15, 110, 164, 0.08);
        }

        .public-calendar-day.empty {
            background: rgba(255, 255, 255, 0.06);
            border-style: dashed;
            box-shadow: none;
        }

        .public-calendar-day.today {
            border: 2px solid #1ab394;
        }

        .public-calendar-date {
            font-size: 20px;
            font-weight: 800;
            color: #0f6ea4;
            margin-bottom: 10px;
        }

        .public-calendar-detail {
            display: flex;
            flex-direction: column;
            gap: 8px;
            max-height: 120px;
            overflow: auto;
            padding-right: 3px;
        }

        .public-calendar-item {
            background: #f7fbff;
            border-left: 4px solid #1c84c6;
            border-radius: 12px;
            padding: 8px 10px;
        }

        .public-calendar-name {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #2f4050;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .public-calendar-status {
            display: inline-block;
            margin-top: 6px;
            font-size: 10px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 999px;
            color: #fff;
        }

        .status-warning { background: #f8ac59; }
        .status-danger { background: #ed5565; }
        .status-success { background: #1ab394; }
        .status-primary { background: #1c84c6; }
        .status-default { background: #a7b1c2; }

        .public-empty-text {
            font-size: 12px;
            color: #98a2b3;
        }

        .floating-note {
            position: fixed;
            right: 26px;
            bottom: 22px;
            padding: 10px 14px;
            border-radius: 16px;
            background: rgba(255, 255, 255, 0.14);
            color: rgba(255, 255, 255, 0.9);
            font-size: 12px;
            backdrop-filter: blur(8px);
        }

        .login-modal {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(5, 23, 36, 0.54);
            padding: 24px;
            z-index: 3000;
        }

        .login-modal.active {
            display: flex;
        }

        .login-card {
            width: 100%;
            max-width: 430px;
            background: #fff;
            border-radius: 24px;
            color: #2f4050;
            box-shadow: 0 30px 70px rgba(0, 0, 0, 0.24);
            overflow: hidden;
        }

        .login-card-header {
            padding: 24px 26px 18px;
            background: linear-gradient(135deg, #f7fbff, #eef8f8);
            border-bottom: 1px solid #e4e7ec;
            display: flex;
            justify-content: space-between;
            gap: 12px;
            align-items: flex-start;
        }

        .login-card-close {
            width: 38px;
            height: 38px;
            border: 0;
            border-radius: 50%;
            background: rgba(15, 110, 164, 0.08);
            color: #0f6ea4;
            font-size: 18px;
        }

        .login-brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .login-brand-logo img {
            width: 70px;
            height: 70px;
            object-fit: contain;
            border-radius: 14px;
            background: #fff;
            padding: 8px;
            box-shadow: 0 10px 22px rgba(28, 132, 198, 0.14);
        }

        .logo-name {
            background: #fff;
            border-radius: 50%;
            width: 70px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 22px rgba(28, 132, 198, 0.14);
            font-size: 34px;
            color: #1c84c6;
            margin: 0;
        }

        .login-brand-copy h3 {
            margin: 0 0 4px;
            color: #1c84c6;
            font-weight: 700;
            font-size: 19px;
        }

        .login-brand-copy p {
            margin: 0;
            font-size: 12px;
            color: #667085;
        }

        .login-card-body {
            padding: 24px 26px 26px;
        }

        .login-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: #eaf5ff;
            color: #1c84c6;
            border-radius: 999px;
            padding: 6px 12px;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            margin-bottom: 12px;
        }

        .login-card-body h4 {
            margin: 0 0 8px;
            font-size: 28px;
            line-height: 1.12;
            color: #2f4050;
            font-weight: 800;
        }

        .login-card-body > p {
            margin: 0 0 20px;
            color: #667085;
            font-size: 14px;
            line-height: 1.6;
        }

        .alert {
            border-radius: 12px;
            padding: 12px 14px;
            border: none;
            background: #fef3f2;
            color: #b42318;
            margin-bottom: 18px;
            font-size: 14px;
            text-align: left;
        }

        .field-label {
            display: block;
            font-size: 12px;
            font-weight: 700;
            color: #475467;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: .5px;
        }

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
            color: #667085;
            text-align: center;
            background-color: #f8fafc;
            border: 1px solid #d0d5dd;
            border-radius: 12px 0 0 12px;
            border-right: none;
            width: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .input-group .form-control {
            position: relative;
            flex: 1 1 auto;
            width: 1%;
            margin-bottom: 0;
            height: 48px;
            border-left: none;
            border-radius: 0 12px 12px 0;
        }

        .form-control {
            display: block;
            width: 100%;
            padding: 10px 12px;
            font-size: 14px;
            line-height: 1.42857;
            color: #344054;
            background-color: #fff;
            border: 1px solid #d0d5dd;
            border-radius: 12px;
            transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
        }

        .form-group {
            margin-bottom: 18px;
        }

        .btn-login-submit {
            background: linear-gradient(135deg, #1c84c6 0%, #23c6c8 100%);
            border: none;
            height: 48px;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: .8px;
            border-radius: 14px;
            box-shadow: 0 12px 24px rgba(28, 132, 198, 0.24);
            transition: all 0.25s;
            color: white;
            width: 100%;
            cursor: pointer;
        }

        .btn-login-submit:hover {
            transform: translateY(-1px);
            box-shadow: 0 16px 28px rgba(28, 132, 198, 0.3);
        }

        .login-note {
            margin-top: 16px;
            font-size: 12px;
            color: #667085;
            text-align: center;
            background: #f8fafc;
            border-radius: 12px;
            padding: 12px 14px;
            border: 1px solid #e4e7ec;
        }

        @media (max-width: 1100px) {
            .screen-header,
            .calendar-toolbar {
                flex-direction: column;
                align-items: flex-start;
            }
        }

        @media (max-width: 768px) {
            .public-shell {
                padding: 14px;
            }

            .calendar-screen {
                padding: 16px;
                border-radius: 20px;
            }

            .screen-title h1 {
                font-size: 28px;
            }

            .public-calendar-grid {
                gap: 8px;
            }

            .public-calendar-day {
                min-height: 128px;
                padding: 10px;
            }

            .month-nav {
                width: 100%;
                justify-content: space-between;
            }

            .month-label {
                min-width: auto;
                flex: 1;
            }

            .floating-note {
                position: static;
                margin: 14px 14px 0;
            }
        }
    </style>
</head>
<body>
    @php
        $dayNames = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];
    @endphp

    <div class="public-shell">
        <section class="calendar-screen animated fadeIn">
            <div class="screen-header">
                <div class="screen-title">
                    <span class="screen-kicker">
                        <i class="fa fa-calendar-check-o"></i> Informasi Reservasi
                    </span>
                    <h1>Kalender Reservasi Catering</h1>
                </div>

                <div class="screen-actions">
                    <div class="month-nav">
                        <a href="{{ $prevMonthUrl }}" class="month-nav-btn"><i class="fa fa-chevron-left"></i></a>
                        <span class="month-label">{{ $monthName }}</span>
                        <a href="{{ $nextMonthUrl }}" class="month-nav-btn"><i class="fa fa-chevron-right"></i></a>
                    </div>
                    <button type="button" class="login-trigger" id="openLoginModal">
                        <i class="fa fa-lock"></i> Login Admin
                    </button>
                </div>
            </div>

            <div class="calendar-toolbar">
                <div class="calendar-legend">
                    <span><i class="fa fa-square text-info"></i> Reserved</span>
                    <span><i class="fa fa-square text-primary"></i> Confirmed</span>
                    <span><i class="fa fa-square text-success"></i> Completed</span>
                    <span><i class="fa fa-square text-danger"></i> Cancelled</span>
                </div>
                <div class="calendar-summary">
                    Periode aktif: <strong>{{ $monthName }}</strong>
                </div>
            </div>

            <div id="publicCalendarGrid" class="public-calendar-grid"></div>
        </section>
    </div>

    <div class="floating-note">
        &copy; {{ date('Y') }} {{ $appName }}
    </div>

    <div class="login-modal" id="loginModal">
        <div class="login-card animated fadeInDown">
            <div class="login-card-header">
                <div class="login-brand">
                    <div class="login-brand-logo">
                        @if($appLogo)
                            <img src="{{ asset($appLogo) }}" alt="Logo">
                        @else
                            <h1 class="logo-name">RC</h1>
                        @endif
                    </div>
                    <div class="login-brand-copy">
                        <h3>{{ $appName }}</h3>
                        <p>{{ $appAddress }}</p>
                    </div>
                </div>
                <button type="button" class="login-card-close" id="closeLoginModal">&times;</button>
            </div>

            <div class="login-card-body">
                <span class="login-badge">
                    <i class="fa fa-lock"></i> Akses Admin
                </span>
                <h4>Masuk ke Sistem Resto Catering</h4>
                <p>Gunakan akun Anda untuk pengelolaan operasional resto.</p>

                @if(session('error'))
                    <div class="alert alert-danger">
                        <i class="fa fa-exclamation-circle"></i>
                        {{ session('error') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('authenticate') }}">
                    @csrf

                    <div class="form-group">
                        <label class="field-label">Username</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-user"></i></span>
                            <input type="text" class="form-control" name="username" placeholder="Username" required autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="field-label">Password</label>
                        <div class="input-group">
                            <span class="input-group-addon"><i class="fa fa-lock"></i></span>
                            <input type="password" class="form-control" name="password" placeholder="Password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-login-submit">LOGIN</button>
                </form>

                <div class="login-note">
                    Copyright Partner in Code Project © 2026
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('js/jquery-3.1.1.min.js') }}"></script>
    <script src="{{ asset('js/popper.min.js') }}"></script>
    <script src="{{ asset('js/bootstrap.js') }}"></script>
    <script>
        const publicCalendarData = @json($calendarMap);
        const publicCalendarMonth = {{ $selectedMonth }};
        const publicCalendarYear = {{ $selectedYear }};
        const publicDayNames = @json($dayNames);
        const loginModal = document.getElementById('loginModal');
        const statusClassMap = {
            reserved: 'status-warning',
            confirmed: 'status-primary',
            completed: 'status-success',
            cancelled: 'status-danger',
        };

        function escapeHtml(value) {
            return String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        function renderPublicCalendar() {
            const grid = document.getElementById('publicCalendarGrid');
            const firstDay = new Date(publicCalendarYear, publicCalendarMonth - 1, 1);
            const jsStart = firstDay.getDay();
            const startOffset = jsStart === 0 ? 6 : jsStart - 1;
            const daysInMonth = new Date(publicCalendarYear, publicCalendarMonth, 0).getDate();
            const today = new Date();
            let html = '';

            publicDayNames.forEach((day) => {
                html += `<div class="public-weekday">${day}</div>`;
            });

            for (let i = 0; i < startOffset; i += 1) {
                html += '<div class="public-calendar-day empty"></div>';
            }

            for (let day = 1; day <= daysInMonth; day += 1) {
                const date = `${publicCalendarYear}-${String(publicCalendarMonth).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
                const items = publicCalendarData[date] || [];
                const isToday = today.getFullYear() === publicCalendarYear
                    && (today.getMonth() + 1) === publicCalendarMonth
                    && today.getDate() === day;

                html += `<div class="public-calendar-day ${isToday ? 'today' : ''}">`;
                html += `<div class="public-calendar-date">${day}</div>`;

                if (items.length > 0) {
                    html += '<div class="public-calendar-detail">';
                    items.forEach((item) => {
                        html += `
                            <div class="public-calendar-item">
                                <span class="public-calendar-name" title="${escapeHtml(item.customer_name)}">${escapeHtml(item.customer_name)}</span>
                                <span class="public-calendar-status ${statusClassMap[item.status] || 'status-default'}">${escapeHtml(item.status)}</span>
                            </div>
                        `;
                    });
                    html += '</div>';
                } else {
                    html += '<div class="public-empty-text">Belum ada reservasi</div>';
                }

                html += '</div>';
            }

            grid.innerHTML = html;
        }

        function openLoginModal() {
            loginModal.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeLoginModal() {
            loginModal.classList.remove('active');
            document.body.style.overflow = '';
        }

        $(document).ready(function() {
            renderPublicCalendar();

            $('#openLoginModal').on('click', openLoginModal);
            $('#closeLoginModal').on('click', closeLoginModal);

            $('#loginModal').on('click', function(event) {
                if (event.target === this) {
                    closeLoginModal();
                }
            });

            $(document).on('keydown', function(event) {
                if (event.key === 'Escape' && loginModal.classList.contains('active')) {
                    closeLoginModal();
                }
            });

            @if(session('error'))
                openLoginModal();
            @endif
        });
    </script>
</body>
</html>
