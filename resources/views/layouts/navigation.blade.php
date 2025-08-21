<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container-fluid px-4">
        <!-- Logo -->
        <a class="navbar-brand fw-bold" href="{{ route('dashboard') }}">
            <i class="fas fa-chart-line me-2"></i>
            {{ config('app.name', 'FinanceApp') }}
        </a>

        <!-- Toggle button for mobile -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation Links -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('dashboard') ? 'active fw-bold' : '' }}" href="{{ route('dashboard') }}">
                        <i class="fas fa-tachometer-alt me-1"></i>
                        <span class="d-lg-inline d-none">{{ __('Dashboard') }}</span>
                        <span class="d-lg-none">{{ __('Home') }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('categories.*') ? 'active fw-bold' : '' }}" href="{{ route('categories.index') }}">
                        <i class="fas fa-tags me-1"></i>
                        {{ __('Categorias') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('transactions.*') ? 'active fw-bold' : '' }}" href="{{ route('transactions.index') }}">
                        <i class="fas fa-exchange-alt me-1"></i>
                        {{ __('Transações') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('reports.*') ? 'active fw-bold' : '' }}" href="{{ route('reports.index') }}">
                        <i class="fas fa-chart-bar me-1"></i>
                        {{ __('Relatórios') }}
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ request()->routeIs('goals.*') ? 'active fw-bold' : '' }}" href="{{ route('goals.index') }}">
                        <i class="fas fa-bullseye me-1"></i>
                        {{ __('Metas') }}
                    </a>
                </li>
            </ul>

            <!-- Quick Actions (Desktop) -->
            <div class="d-none d-lg-flex me-3">
                <div class="btn-group" role="group">
                    <a href="{{ route('transactions.create') }}" class="btn btn-success btn-sm" title="Nova Transação">
                        <i class="fas fa-plus me-1"></i>
                        {{ __('Transação') }}
                    </a>
                    <a href="{{ route('categories.create') }}" class="btn btn-outline-light btn-sm" title="Nova Categoria">
                        <i class="fas fa-tag"></i>
                    </a>
                </div>
            </div>

            <!-- User Dropdown -->
            <div class="dropdown">
                <button class="btn btn-outline-light dropdown-toggle d-flex align-items-center" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle me-2"></i>
                    <span class="d-none d-md-inline">{{ Auth::user()->name }}</span>
                    <span class="d-md-none">{{ substr(Auth::user()->name, 0, 1) }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                    <li>
                        <h6 class="dropdown-header">
                            <i class="fas fa-user me-1"></i>
                            {{ Auth::user()->name }}
                        </h6>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('profile.edit') }}">
                            <i class="fas fa-cog me-2"></i>
                            {{ __('Configurações') }}
                        </a>
                    </li>
                    <li class="d-lg-none"><hr class="dropdown-divider"></li>
                    <li class="d-lg-none">
                        <a class="dropdown-item" href="{{ route('transactions.create') }}">
                            <i class="fas fa-plus me-2"></i>
                            {{ __('Nova Transação') }}
                        </a>
                    </li>
                    <li class="d-lg-none">
                        <a class="dropdown-item" href="{{ route('categories.create') }}">
                            <i class="fas fa-tag me-2"></i>
                            {{ __('Nova Categoria') }}
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                {{ __('Sair') }}
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>
