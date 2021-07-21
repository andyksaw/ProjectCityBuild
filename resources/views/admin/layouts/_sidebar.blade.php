<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link {{ request()->is('panel') ? 'active' : '' }}" aria-current="page" href="{{ route('front.panel.index') }}">
                    <i class="fas fa-home fa-fw"></i>
                    Home
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Users</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link {{ request()->is('panel/accounts*') ? 'active' : '' }}" href="{{ route('front.panel.accounts.index') }}">
                    <i class="fas fa-users fa-fw"></i>
                    Accounts
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('panel/minecraft-players*') ? 'active' : '' }} " href="{{ route('front.panel.minecraft-players.index') }}">
                    <i class="fas fa-cube"></i>
                    Minecraft Players
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ request()->is('panel/groups*') ? 'active' : '' }} " href="{{ route('front.panel.groups.index') }}">
                    <i class="fas fa-users"></i>
                    Groups
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Transactions</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link {{ request()->is('panel/donations*') ? 'active' : '' }}" href="{{ route('front.panel.donations.index') }}">
                    <i class="fas fa-credit-card fa-fw"></i>
                    Donations
                </a>
            </li>
        </ul>
    </div>
</nav>
