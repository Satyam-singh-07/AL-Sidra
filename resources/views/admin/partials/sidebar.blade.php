<div id="sidebar-wrapper">
    <div class="sidebar-heading">
        <img src="{{ asset('assets/logo.PNG') }}" alt="Logo" class="logo-img">
        AL SIDRA
    </div>

    <div class="sidebar-content">
        <div class="list-group list-group-flush">

            <a href="{{route('dashboard')}}"
               class="list-group-item list-group-item-action {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>

            <a href="" class="list-group-item list-group-item-action">
                <i class="fas fa-book-open"></i> Quran PDF
            </a>

            <a href="" class="list-group-item list-group-item-action">
                <i class="fas fa-praying-hands"></i> Namaz Content
            </a>

            <a href="" class="list-group-item list-group-item-action">
                <i class="fas fa-mosque"></i> Masjid Listing
            </a>

        </div>
    </div>
</div>
