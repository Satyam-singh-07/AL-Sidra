<nav class="navbar navbar-expand-lg navbar-custom px-3">
    <button class="btn btn-outline-success" id="menu-toggle">
        <i class="fas fa-bars"></i>
    </button>

    <div class="ms-auto d-flex align-items-center">
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none text-dark dropdown-toggle"
               data-bs-toggle="dropdown">
                <div class="bg-success text-white rounded-circle d-flex justify-content-center align-items-center me-2"
                     style="width: 35px; height: 35px;">
                    A
                </div>
                <strong>Admin</strong>
            </a>

            <ul class="dropdown-menu dropdown-menu-end shadow">
                <li><a class="dropdown-item" href="#">Profile</a></li>
                <li><a class="dropdown-item" href="#">Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <form method="POST" action="{{route('logout')}}">
                        @csrf
                        <button class="dropdown-item text-danger">Logout</button>
                    </form>
                </li>
            </ul>
        </div>
    </div>
</nav>
