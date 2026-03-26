<nav id="navbar-main" class="navbar is-fixed-top">
  <div class="navbar-brand">
    <a class="navbar-item mobile-aside-button">
      <span class="icon"><i class="mdi mdi-forwardburger mdi-24px"></i></span>
    </a>
    <div class="navbar-item">
      <div class="control"><input placeholder="Search links, commissions..." class="input"></div>
    </div>
  </div>
  <div class="navbar-brand is-right">
    <a class="navbar-item --jb-navbar-menu-toggle" data-target="navbar-menu">
      <span class="icon"><i class="mdi mdi-dots-vertical mdi-24px"></i></span>
    </a>
  </div>
  <div class="navbar-menu" id="navbar-menu">
    <div class="navbar-end">
      <div class="navbar-item dropdown has-divider has-user-avatar">
        <a class="navbar-link">
          <div class="user-avatar">
            <img src="https://avatars.dicebear.com/v2/initials/affiliate-user.svg" alt="Affiliate" class="rounded-full">
          </div>
          <div class="is-user-name"><span>{{ auth()->guard('affiliate')->user()?->name ?? 'Affiliate Panel' }}</span></div>
          <span class="icon"><i class="mdi mdi-chevron-down"></i></span>
        </a>
        <div class="navbar-dropdown">
          <a href="{{ route('affiliates.account.profile') }}" class="navbar-item">
            <span class="icon"><i class="mdi mdi-account-circle"></i></span>
            <span>Profile</span>
          </a>
          <a href="{{ route('affiliates.account.settings') }}" class="navbar-item">
            <span class="icon"><i class="mdi mdi-cog-outline"></i></span>
            <span>Settings</span>
          </a>
          <hr class="navbar-divider">
          <a class="navbar-item"
            onclick="event.preventDefault(); document.getElementById('affiliate-logout-form').submit();">
            <span class="icon"><i class="mdi mdi-logout"></i></span>
            <span>Log Out</span>
          </a>
          <form id="affiliate-logout-form" action="{{ route('affiliates.logout') }}" method="POST" style="display: none;">
            @csrf
          </form>
        </div>
      </div>
    </div>
  </div>
</nav>
