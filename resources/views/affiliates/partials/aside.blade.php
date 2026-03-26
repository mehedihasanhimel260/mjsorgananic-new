<aside class="aside is-placed-left is-expanded">
    <div class="aside-tools">
        <div>
            <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer"
                class="inline-flex items-center px-4 py-2 bg-black rounded-lg">
                <span class="text-white text-lg font-medium">MJS</span>
                <span class="text-green-400 text-lg font-black ml-1">Affiliate</span>
            </a>
        </div>
    </div>
    <div class="menu is-menu-main">
        <p class="menu-label">General</p>
        <ul class="menu-list">
            <li class="active">
                <a href="{{ route('affiliates.dashboard') }}">
                    <span class="icon"><i class="mdi mdi-view-dashboard-outline"></i></span>
                    <span class="menu-item-label">Dashboard</span>
                </a>
            </li>
        </ul>
        <p class="menu-label">Affiliate Tools</p>
        <ul class="menu-list">
            <li>
                <a href="{{ route('affiliates.links.index') }}" class="has-icon">
                    <span class="icon"><i class="mdi mdi-link-variant"></i></span>
                    <span class="menu-item-label">My Links</span>
                </a>
            </li>
            <li>
                <a href="{{ route('affiliates.orders.index') }}" class="has-icon">
                    <span class="icon"><i class="mdi mdi-cart-outline"></i></span>
                    <span class="menu-item-label">Orders</span>
                </a>
            </li>
            <li>
                <a href="{{ route('affiliates.commissions.index') }}" class="has-icon">
                    <span class="icon"><i class="mdi mdi-cash-multiple"></i></span>
                    <span class="menu-item-label">Commissions</span>
                </a>
            </li>
            <li>
                <a href="{{ route('affiliates.wallet.index') }}" class="has-icon">
                    <span class="icon"><i class="mdi mdi-wallet-outline"></i></span>
                    <span class="menu-item-label">Wallet</span>
                </a>
            </li>
            <li>
                <a href="{{ route('affiliates.wallet.index') }}#withdraw-request" class="has-icon">
                    <span class="icon"><i class="mdi mdi-cash-fast"></i></span>
                    <span class="menu-item-label">Withdraw</span>
                </a>
            </li>
        </ul>
        <p class="menu-label">Account</p>
        <ul class="menu-list">
            <li>
                <a href="{{ route('affiliates.account.profile') }}" class="has-icon">
                    <span class="icon"><i class="mdi mdi-account-edit-outline"></i></span>
                    <span class="menu-item-label">Profile</span>
                </a>
            </li>
            <li>
                <a href="{{ route('affiliates.account.settings') }}" class="has-icon">
                    <span class="icon"><i class="mdi mdi-cog-outline"></i></span>
                    <span class="menu-item-label">Settings</span>
                </a>
            </li>
        </ul>
    </div>
</aside>
