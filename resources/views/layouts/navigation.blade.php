<style>
    .profile-img{
        width: 48px;
        height: 48px;
        object-fit: cover;
    }
</style>
<nav class="navbar-default navbar-static-side" role="navigation">
    <div class="sidebar-collapse">
        <ul class="nav metismenu" id="side-menu">
            @php
                // Cek dari session karena menggunakan middleware checklogin
                $isLoggedIn = Session::has('name') && Session::has('level');
                $userName = Session::get('name', 'Guest');
                $userLevel = Session::get('level', 'User');
                $userFoto = Session::get('foto', null);
            @endphp

            @if($isLoggedIn)
                @php
                    $setting = \App\Models\Setting::first();

                    function singkatPerusahaan($nama) {
                        $parts = explode(' ', trim($nama));
                        $result = '';
                        foreach ($parts as $p) {
                            if (strlen($p) > 2) {
                                $result .= strtoupper(substr($p, 0, 1));
                            }
                        }
                        return $result ?: 'IN+';
                    }

                    function singkatNama($nama) {
                        $parts = explode(' ', trim($nama));
                        if (count($parts) <= 1) return $nama;

                        $namaDepan = array_shift($parts);
                        $inisial = array_map(function($p) { 
                            return strtoupper(substr($p, 0, 1)) . '.'; 
                        }, $parts);

                        return $namaDepan . ' ' . implode('', $inisial);
                    }

                    $displayName = singkatNama($userName);
                    $namaPendek = singkatPerusahaan($setting->nama_perusahaan ?? 'Perusahaan');
                    
                    // Cek foto
                    $fotoPath = public_path('uploads/foto/' . $userFoto);
                    $hasFoto = $userFoto && file_exists($fotoPath);
                @endphp

                <!-- Nav Header - Inspinia Style -->
                <li class="nav-header">
                    <div class="dropdown profile-element">
                        @if($hasFoto)
                            <img alt="image"
                                class="rounded-circle profile-img"
                                src="{{ asset('uploads/foto/'.$userFoto) }}?t={{ time() }}"/>
                        @else
                            <img alt="image"
                                class="rounded-circle profile-img"
                                src="{{ asset('user.png') }}"/>
                        @endif
                        <a data-toggle="dropdown" class="dropdown-toggle" href="#">
                            <span class="block m-t-xs font-bold">{{ $displayName }}</span>
                            <span class="text-muted text-xs block">{{ strtoupper($userLevel) }} <b class="caret"></b></span>
                        </a>
                        <ul class="dropdown-menu animated fadeInRight m-t-xs">
                            <li><a class="dropdown-item" href="">Profile</a></li>
                            <li><a class="dropdown-item" href="">Pengaturan</a></li>
                            <li class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">Logout</a></li>
                        </ul>
                    </div>
                    <div class="logo-element">
                        {{ $namaPendek }}
                    </div>
                </li>

                <!-- Search Bar -->
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

                @php
                    $currentUserLevel = $userLevel;
                    $menuCount = 0;
                    $staticMenus = [
                        [
                            'label' => 'Dashboard',
                            'route' => 'cuti.dashboard',
                            'icon' => 'fa-th-large',
                        ],
                        [
                            'label' => 'Kalender Cuti',
                            'route' => 'cuti.calendar',
                            'icon' => 'fa-calendar',
                        ],
                    ];
                @endphp

                @foreach ($staticMenus as $staticMenu)
                    @php
                        $hasRoute = Route::has($staticMenu['route']);
                        $isStaticActive = $hasRoute && request()->routeIs($staticMenu['route']);
                    @endphp
                    @if($hasRoute)
                        @php $menuCount++; @endphp
                        <li class="{{ $isStaticActive ? 'active' : '' }}">
                            <a href="{{ route($staticMenu['route']) }}">
                                <i class="fa {{ $staticMenu['icon'] }}"></i>
                                <span class="nav-label">{{ $staticMenu['label'] }}</span>
                            </a>
                        </li>
                    @endif
                @endforeach

                @foreach ($menus as $item)
                    @php
                        // Cek akses menu utama
                        $menuRoles = [];
                        if (!empty($item->role)) {
                            $cleanedRole = trim($item->role, ';');
                            if (!empty($cleanedRole)) {
                                $menuRoles = explode(';', $cleanedRole);
                            }
                        }
                        
                        $hasAccess = in_array($currentUserLevel, $menuRoles);
                        
                        // Cek apakah menu memiliki children yang bisa diakses
                        $hasAccessibleChildren = false;
                        $accessibleChildren = [];
                        
                        if ($item->children && $item->children->count() > 0) {
                            foreach ($item->children as $child) {
                                $childRoles = [];
                                if (!empty($child->role)) {
                                    $cleanedChildRole = trim($child->role, ';');
                                    if (!empty($cleanedChildRole)) {
                                        $childRoles = explode(';', $cleanedChildRole);
                                    }
                                }
                                
                                if (in_array($currentUserLevel, $childRoles)) {
                                    $hasAccessibleChildren = true;
                                    $accessibleChildren[] = $child;
                                }
                            }
                        }
                        
                        // Tampilkan menu jika memiliki akses langsung atau memiliki children yang bisa diakses
                        $showMenu = $hasAccess || $hasAccessibleChildren;
                        
                        if ($showMenu) {
                            $menuCount++;
                        }
                        
                        // Cek apakah menu ini aktif
                        $isActive = request()->routeIs($item->link);
                        $isChildActive = false;
                        
                        foreach ($accessibleChildren as $child) {
                            if (request()->routeIs($child->link)) {
                                $isChildActive = true;
                                break;
                            }
                        }
                    @endphp

                    @if($showMenu)
                        <li class="{{ ($isActive || $isChildActive) ? 'active' : '' }}">
                            <a href="{{ !$hasAccessibleChildren && Route::has($item->link) ? route($item->link) : '#' }}" 
                               @if($hasAccessibleChildren)
                                   data-toggle="collapse" 
                                   href="#menu-{{ $item->id }}" 
                                   aria-expanded="{{ $isChildActive ? 'true' : 'false' }}" 
                                   aria-controls="menu-{{ $item->id }}"
                               @endif>
                                @php
                                    $icon = $item->icon;
                                    if (strpos($icon, 'bi-') === 0) {
                                        $icon = str_replace('bi-', 'fa-', $icon);
                                    }
                                    if (empty($icon) || $icon == 'fa-') {
                                        $icon = 'fa-folder-o';
                                    }
                                @endphp
                                <i class="{{ $icon }}"></i>
                                <span class="nav-label">{{ $item->name }}</span>
                                @if($hasAccessibleChildren)
                                    <span class="fa arrow"></span>
                                @endif
                            </a>

                            @if($hasAccessibleChildren && count($accessibleChildren) > 0)
                                <ul class="nav nav-second-level collapse {{ $isChildActive ? 'in' : '' }}" 
                                    id="menu-{{ $item->id }}" 
                                    style="{{ $isChildActive ? 'display: block;' : '' }}">
                                    @foreach($accessibleChildren as $child)
                                        @php
                                            $childIcon = $child->icon ?? 'fa-circle-o';
                                            if (strpos($childIcon, 'bi-') === 0) {
                                                $childIcon = str_replace('bi-', 'fa-', $childIcon);
                                            }
                                            
                                            $hasLink = !empty($child->link) && Route::has($child->link);
                                            $isChildActiveItem = request()->routeIs($child->link);
                                        @endphp
                                        <li class="{{ $isChildActiveItem ? 'active' : '' }}">
                                            <a href="{{ $hasLink ? route($child->link) : '#' }}">
                                                <i class="{{ $childIcon }}"></i>
                                                {{ $child->name }}
                                            </a>
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </li>
                    @endif
                @endforeach

                @if($menuCount == 0)
                    <li class="text-center text-muted p-3">
                        <small>Tidak ada menu untuk level "{{ $currentUserLevel }}"</small>
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

<!-- Logout Form -->
<form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
    @csrf
</form>
