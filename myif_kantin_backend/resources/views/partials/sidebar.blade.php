<nav class="sidebar">
  <div class="sidebar-header">
      <a href="{{ route('dashboard.index') }}" class="sidebar-brand">
          myIF<span>-Kantin</span>
      </a>
      <div class="sidebar-toggler not-active">
          <span></span>
          <span></span>
          <span></span>
      </div>
  </div>

  <div class="sidebar-body">
      <ul class="nav">
          <li class="nav-item {{ request()->routeIs('dashboard.*') ? 'active' : '' }}">
              <a href="{{ route('dashboard.index') }}" class="nav-link">
                  <i class="link-icon" data-feather="home"></i>
                  <span class="link-title">Dashboard</span>
              </a>
          </li>

          <li class="nav-item nav-category">Master Data</li>

          <li class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
              <a href="{{ route('users.index') }}" class="nav-link">
                  <i class="link-icon" data-feather="users"></i>
                  <span class="link-title">Users</span>
              </a>
          </li>

          <li class="nav-item {{ request()->routeIs('vendors.*') ? 'active' : '' }}">
              <a href="{{ route('vendors.index') }}" class="nav-link">
                  <i class="link-icon" data-feather="home"></i>
                  <span class="link-title">Vendors</span>
              </a>
          </li>

          <li class="nav-item {{ request()->routeIs('menus.*') ? 'active' : '' }}">
              <a href="{{ route('menus.index') }}" class="nav-link">
                  <i class="link-icon" data-feather="menu"></i>
                  <span class="link-title">Menus</span>
              </a>
          </li>

          <li class="nav-item nav-category">Orders</li>

          <li class="nav-item {{ request()->routeIs('orders.*') ? 'active' : '' }}">
              <a href="{{ route('orders.index') }}" class="nav-link">
                  <i class="link-icon" data-feather="shopping-cart"></i>
                  <span class="link-title">Orders</span>
              </a>
          </li>
      </ul>
  </div>
</nav>