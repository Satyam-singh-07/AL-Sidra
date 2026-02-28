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

            @php $user = auth()->user(); @endphp

            {{-- Masjids --}}
            @if ($user->canAccess('manage_masjids'))
                <a href="{{ route('masjids.index') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('masjids.index') ? 'active' : '' }}">
                    <i class="fas fa-mosque"></i> Masjid Listing
                </a>
            @endif

            {{-- Madarsas --}}
            @if ($user->canAccess('manage_madarsas'))
                <a href="{{ route('madarsas.index') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('madarsas.index') ? 'active' : '' }}">
                    <i class="fas fa-school"></i> Madrasa Listing
                </a>
            @endif

            {{-- Restaurants --}}
            @if ($user->canAccess('manage_restaurants'))
                <a href="{{ route('restaurants.index') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('restaurants.index') ? 'active' : '' }}">
                    <i class="fas fa-utensils"></i> Restaurants Requests
                </a>
            @endif

            {{-- Yateems --}}
            @if ($user->canAccess('manage_yateems'))
                <a href="{{ route('yateems-helps.index') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('yateems-helps.index') ? 'active' : '' }}">
                    <i class="fas fa-hands-helping"></i> Yateem Listing
                </a>
            @endif

            {{-- Member Categories --}}
            @if ($user->canAccess('manage_member_categories'))
                <a href="{{ route('membercategories.index') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('membercategories.index') ? 'active' : '' }}">
                    <i class="fas fa-users-cog"></i> Member Categories
                </a>
            @endif

            {{-- Communities --}}
            @if ($user->canAccess('manage_communities'))
                <a href="{{ route('communities.index') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('communities.index') ? 'active' : '' }}">
                    <i class="fas fa-sitemap"></i> Communities
                </a>
            @endif

            {{-- Users --}}
            @if ($user->canAccess('manage_users'))
                <a href="{{ route('users.index') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('users.index') ? 'active' : '' }}">
                    <i class="fas fa-user"></i> Users
                </a>
            @endif

            {{-- Members --}}
            @if ($user->canAccess('manage_members'))
                <a href="{{ route('members.index') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('members.index') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> Members
                </a>
            @endif

            {{-- Banners --}}
            @if ($user->canAccess('manage_banners'))
                <a href="{{ route('banners.index') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('banners.index') ? 'active' : '' }}">
                    <i class="fas fa-image"></i> Banners
                </a>
            @endif

            {{-- Muslim Updates --}}
            @if ($user->canAccess('manage_hot_topics'))
                <a href="{{ route('hot-topics.index') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('hot-topics.index') ? 'active' : '' }}">
                    <i class="fas fa-fire"></i> Muslim Updates
                </a>
            @endif

            {{-- Ongoing Work --}}
            @if ($user->canAccess('manage_ongoing_works'))
                <a href="{{ route('ongoing-work.index') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('ongoing-work.index') ? 'active' : '' }}">
                    <i class="fas fa-hard-hat"></i> Ongoing Work
                </a>
            @endif

            {{-- Religion Info --}}
            @if ($user->canAccess('manage_religious_info'))
                <a href="{{ route('religion-info.index') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('religion-info.index') ? 'active' : '' }}">
                    <i class="fas fa-info-circle"></i> Religion Info
                </a>
            @endif

            {{-- Videos --}}
            @if ($user->canAccess('manage_videos'))
                <a href="{{ route('videos.index') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('videos.index') ? 'active' : '' }}">
                    <i class="fas fa-video"></i> Videos
                </a>
            @endif

            {{-- Roles --}}
            @if ($user->canAccess('manage_roles'))
                <a href="{{ route('roles.index') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('roles.index') ? 'active' : '' }}">
                    <i class="fas fa-user-tag"></i> Roles & Permissions
                </a>
            @endif

            {{-- Permissions --}}
            @if ($user->canAccess('manage_permissions'))
                <a href="{{ route('permissions.index') }}"
                    class="list-group-item list-group-item-action {{ request()->routeIs('permissions.index') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> Users & Roles
                </a>
            @endif

        </div>
    </div>
</div>
