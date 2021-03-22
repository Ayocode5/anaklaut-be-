<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="#">
        <div class="sidebar-brand-icon rotate-n-15">
            <i class="fas fa-fish"></i>
        </div>
        <div class="sidebar-brand-text mx-3">AnakLaut</div>
    </a>

    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item {{ request()->is('admin') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.dashboard') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Administrasi
    </div>

    <!-- Nav Item - Pages Collapse Menu -->
    <li class="nav-item {{ request()->is('admin/products') | request()->is('admin/products/add') ? 'active' : '' }}">
        <a class="nav-link collapsed" href="#" data-toggle="collapse" data-target="#collapseTwo"
            aria-expanded="true" aria-controls="collapseTwo">
            <i class="fas fa-box"></i>
            <span>Produk</span>
        </a>
        <div id="collapseTwo" class="collapse {{ request()->is('admin/products') | request()->is('admin/products/add')  ? 'show' : ''}}" aria-labelledby="headingTwo" data-parent="#accordionSidebar">
            <div class="bg-white py-2 collapse-inner rounded">
                <h6 class="collapse-header">Atur produkmu!</h6>
                <a class="collapse-item {{ request()->is('admin/products') ? 'active' : '' }}" 
                href="{{ url('/admin/products') }}">List Produk</a>
                <a class="collapse-item {{ request()->is('admin/products/add') ? 'active' : '' }}" 
                href="{{ url('/admin/products/add') }}">Tambah Produk</a>
            </div>
        </div>
    </li>

    {{-- <li class="nav-item {{ request()->is('admin/orders') ? 'active' : '' }}">
        <a class="nav-link" href="{{ url('/admin/orders') }}">
            <i class="fas fa-user"></i>
            <span>Pesanan</span></a>
    </li> --}}

    <li class="nav-item {{ request()->is('admin/transactions') ? 'active' : '' }}">
        <a class="nav-link" href="{{ url('/admin/transactions') }}">
            <i class="fas fa-wallet"></i>
            <span>Transaksi</span></a>
    </li>

    <!-- Heading -->
    <div class="sidebar-heading">
        Reviews
    </div>

    <li class="nav-item {{ request()->is('admin/reviews') ? 'active' : '' }}">
        <a class="nav-link" href="{{ url('/admin/reviews') }}">
            <i class="fas fa-star"></i>
            <span>Review Pembeli</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

    <!-- Sidebar Toggler (Sidebar) -->
    <div class="text-center d-none d-md-inline">
        <button class="rounded-circle border-0" id="sidebarToggle"></button>
    </div>

   

</ul>