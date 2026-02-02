<div id="sidebar-wrapper">
    <div class="sidebar-heading">
        <img src="{{ asset('assets/logo.PNG') }}" alt="Logo" class="logo-img">
        AL SIDRA
    </div>

    <div class="sidebar-content">
        <div class="list-group list-group-flush">

            <a href="{{ route('dashboard') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <i class="fas fa-tachometer-alt"></i> Dashboard
            </a>

            <a href="" class="list-group-item list-group-item-action">
                <i class="fas fa-book-open"></i> Quran PDF
            </a>

            <a href="" class="list-group-item list-group-item-action">
                <i class="fas fa-praying-hands"></i> Namaz Content
            </a>

            <a href="{{ route('masjids.index') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('masjids.index') ? 'active' : '' }}">
                <i class="fas fa-mosque"></i> Masjid Listing
            </a>

            <a href="{{ route('restaurants.index') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('restaurants.index') ? 'active' : '' }}">
                <i class="fas fa-utensils"></i> Restaurants Requests
            </a>

            <a href="{{ route('yateems-helps.index') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('yateems-helps.index') ? 'active' : '' }}">
                <i class="fas fa-hands-helping"></i> Yateem Listing
            </a>

            <a href="{{ route('membercategories.index') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('membercategories.index') ? 'active' : '' }}">
                <i class="fas fa-users-cog"></i> Member Categories
            </a>

            <a href="{{ route('communities.index') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('communities.index') ? 'active' : '' }}">
                <i class="fas fa-sitemap"></i> Communities
            </a>

            <a href="{{ route('users.index') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('users.index') ? 'active' : '' }}">
                <i class="fas fa-user"></i> Users
            </a>

            <a href="{{ route('members.index') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('members.index') ? 'active' : '' }}">
                <i class="fas fa-users"></i> Members
            </a>

            <a href="{{ route('banners.index') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('banners.index') ? 'active' : '' }}">
                <i class="fas fa-image"></i> Banners
            </a>

            <a href="{{ route('hot-topics.index') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('hot-topics.index') ? 'active' : '' }}">
                <i class="fas fa-fire"></i> Hot Topics
            </a>

            <a href="{{ route('ongoing-work.index') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('ongoing-work.index') ? 'active' : '' }}">
                <i class="fas fa-hard-hat"></i> Ongoing Work
            </a>

            <a href="{{ route('religion-info.index') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('religion-info.index') ? 'active' : '' }}">
                <i class="fas fa-info-circle"></i> Religion Info
            </a>

            <a href="{{ route('videos.index') }}"
                class="list-group-item list-group-item-action {{ request()->routeIs('videos.index') ? 'active' : '' }}">
                <i class="fas fa-video"></i> Videos
            </a>
        </div>
    </div>
</div>
