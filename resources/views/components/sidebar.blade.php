@auth
    <div class="main-sidebar sidebar-style-2">
        <aside id="sidebar-wrapper">
            <div class="sidebar-brand">
                <a href="">SKPD Notice Pajak</a>
            </div>
            <div class="sidebar-brand sidebar-brand-sm">
                <a href="">SKPD </a>
            </div>
            <ul class="sidebar-menu">
                <li class="menu-header">Dashboard</li>
                <li class="{{ Request::is('home') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ url('home') }}"><i class="fas fa-fire"></i><span>Dashboard</span></a>
                </li>
                @if (Auth::user()->role == 'superadmin')
                    <li class="menu-header">Master Data</li>

                    <li class="{{ Request::is('lokasi*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('lokasi.index') }}"><i class="fas fa-map-marker-alt"></i>
                            <span>Manajemen Lokasi</span></a>
                    </li>

                    <li class="{{ Request::is('layanan*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('layanan.index') }}"><i class="fas fa-concierge-bell"></i>
                            <span>Manajemen Layanan</span></a>
                    </li>

                    <li class="{{ Request::is('admin-users*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin-users.index') }}"><i class="fas fa-users-cog"></i>
                            <span>Manajemen Admin</span></a>
                    </li>
                @endif
                @if (Auth::user()->role == 'admin')
                    <li class="menu-header">Master Data</li>

                    <li class="{{ Request::is('kasir-users*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('kasir-users.index') }}"><i class="fas fa-user-tie"></i>
                            <span>Manajemen Kasir</span></a>
                    </li>

                    <li class="menu-header">Monitoring</li>

                    <li class="{{ Request::is('penerimaan-notices*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('admin.penerimaan-notices.index') }}"><i
                                class="fas fa-file-invoice"></i>
                            <span>Penerimaan Notice</span></a>
                    </li>
                @endif
                @if (Auth::user()->role == 'kasir')
                    <li class="menu-header">Transaksi</li>

                    <li class="{{ Request::is('penerimaan-notices*') ? 'active' : '' }}">
                        <a class="nav-link" href="{{ route('penerimaan-notices.index') }}"><i
                                class="fas fa-file-invoice"></i>
                            <span>Penerimaan Notice</span></a>
                    </li>
                @endif
                <!-- profile ganti password -->

            </ul>
        </aside>
    </div>
@endauth
