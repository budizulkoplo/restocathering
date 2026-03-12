@php
    use App\Models\RestaurantProfile;
    use App\Models\Setting;
    use App\Models\User;
    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Schema;
    use Illuminate\Support\Facades\Session;

    $isLoggedIn = Session::has('name');
    $userName = Session::get('name', 'Guest');
    $userLevel = Session::get('level', 'User');
    $userFoto = Session::get('foto');
    $currentUser = $isLoggedIn ? User::query()->find(Session::get('user_id')) : null;
    $profile = class_exists(RestaurantProfile::class) ? RestaurantProfile::query()->first() : null;
    $legacySetting = Schema::hasTable('setting') ? Setting::query()->first() : null;
    $appName = $profile->name ?? $legacySetting->nama_perusahaan ?? 'Resto Catering';
    $displayName = collect(explode(' ', trim($userName)))->filter()->values();
    $shortName = $displayName->count() > 1
        ? $displayName->first() . ' ' . $displayName->slice(1)->map(fn ($item) => strtoupper(substr($item, 0, 1)) . '.')->implode('')
        : $userName;
    $companyShort = collect(explode(' ', trim($appName)))->filter()->map(fn ($item) => strtoupper(substr($item, 0, 1)))->implode('');
    $companyShort = $companyShort ?: 'RC';
    $fotoPath = $userFoto ? public_path('uploads/foto/' . $userFoto) : null;
    $hasFoto = $fotoPath && file_exists($fotoPath);
    $canAccess = function (string $permission) use ($currentUser, $userLevel) {
        if (! $currentUser) {
            return false;
        }

        $isBootstrapAdmin = in_array(strtolower((string) $userLevel), ['admin', 'administrator', 'superadmin'], true);

        if (method_exists($currentUser, 'can') && Schema::hasTable('permissions')) {
            $hasAssignedRoles = method_exists($currentUser, 'roles') && $currentUser->roles()->exists();

            if (! $hasAssignedRoles && $isBootstrapAdmin) {
                return true;
            }

            return $currentUser->can($permission) || $currentUser->hasRole('admin');
        }

        return $isBootstrapAdmin;
    };

    $menuGroups = [
        [
            'heading' => 'Operasional',
            'items' => [
                ['label' => 'Dashboard', 'route' => 'dashboard', 'icon' => 'fa-th-large', 'permission' => 'dashboard.view'],
                ['label' => 'Kalender Catering', 'route' => 'catering.calendar', 'icon' => 'fa-calendar', 'permission' => 'catering-orders.view'],
                ['label' => 'Order Resto', 'route' => 'restaurant-orders.index', 'icon' => 'fa-cutlery', 'permission' => 'restaurant-orders.view'],
                ['label' => 'Display Dapur', 'route' => 'kitchen.index', 'icon' => 'fa-television', 'permission' => 'restaurant-orders.view'],
            ],
        ],
        [
            'heading' => 'Master Data',
            'items' => [
                ['label' => 'Customer', 'route' => 'customers.index', 'icon' => 'fa-users', 'permission' => 'customers.view'],
                ['label' => 'Bahan Baku', 'route' => 'ingredients.index', 'icon' => 'fa-cubes', 'permission' => 'ingredients.view'],
                ['label' => 'Menu', 'route' => 'menu-items.index', 'icon' => 'fa-book', 'permission' => 'menu-items.view'],
                ['label' => 'Meja & QR', 'route' => 'tables.index', 'icon' => 'fa-qrcode', 'permission' => 'dining-tables.view'],
            ],
        ],
        [
            'heading' => 'Manajemen',
            'items' => [
                ['label' => 'Belanja Bahan', 'route' => 'purchases.index', 'icon' => 'fa-shopping-cart', 'permission' => 'purchases.view'],
                ['label' => 'Stok Opname', 'route' => 'stock-opnames.index', 'icon' => 'fa-check-square-o', 'permission' => 'stock-opnames.view'],
                ['label' => 'Laporan', 'route' => 'reports.index', 'icon' => 'fa-line-chart', 'permission' => 'reports.view'],
                ['label' => 'Setting', 'route' => 'settings.index', 'icon' => 'fa-cogs', 'permission' => 'settings.view'],
            ],
        ],
    ];
@endphp

<style>
    .profile-img {
        width: 48px;
        height: 48px;
        object-fit: cover;
    }

    .nav-section-title {
        padding: 14px 20px 6px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: .8px;
        text-transform: uppercase;
        color: #a7b1c2;
    }
</style>

<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav metismenu" id="side-menu">
            @if ($isLoggedIn)
                <li class="nav-header">
                    <div class="dropdown profile-element">
                        <img alt="image" class="rounded-circle profile-img" src="{{ $hasFoto ? asset('uploads/foto/'.$userFoto) . '?t=' . time() : asset('user.png') }}">
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <span class="block m-t-xs font-bold">{{ $shortName }}</span>
                            <span class="text-muted text-xs block">{{ strtoupper($userLevel) }} <b class="caret"></b></span>
                        </a>
                        <ul class="dropdown-menu animated fadeInRight m-t-xs">
                            <li><a class="dropdown-item" href="{{ route('settings.index') }}">Pengaturan</a></li>
                            <li class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="{{ route('logout') }}">Logout</a></li>
                        </ul>
                    </div>
                    <div class="logo-element">{{ $companyShort }}</div>
                </li>

                <li class="search-bar" style="padding: 10px 15px;">
                    <div class="input-group">
                        <input type="text" id="menuSearch" class="form-control" placeholder="Cari menu...">
                        <span class="input-group-btn">
                            <button class="btn btn-default" type="button">
                                <i class="fa fa-search"></i>
                            </button>
                        </span>
                    </div>
                    <small id="searchResultCount" class="text-muted" style="font-size: 0.7rem; display: block; margin-top: 5px;"></small>
                </li>

                @php $visibleMenus = 0; @endphp

                @foreach ($menuGroups as $group)
                    @php
                        $groupItems = collect($group['items'])->filter(fn ($item) => Route::has($item['route']) && $canAccess($item['permission']));
                    @endphp

                    @if ($groupItems->isNotEmpty())
                        <li class="nav-section-title">{{ $group['heading'] }}</li>
                        @foreach ($groupItems as $item)
                            @php $visibleMenus++; @endphp
                            <li class="{{ request()->routeIs($item['route']) ? 'active' : '' }}">
                                <a href="{{ route($item['route']) }}">
                                    <i class="fa {{ $item['icon'] }}"></i>
                                    <span class="nav-label">{{ $item['label'] }}</span>
                                </a>
                            </li>
                        @endforeach
                    @endif
                @endforeach

                @if ($visibleMenus === 0)
                    <li class="text-center text-muted p-3">
                        <small>Tidak ada menu untuk role saat ini.</small>
                    </li>
                @endif
            @else
                <li class="text-center text-white p-4">
                    <p>Silakan login terlebih dahulu</p>
                </li>
            @endif
        </ul>
    </div>
</nav>
