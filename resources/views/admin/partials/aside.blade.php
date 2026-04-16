<aside class="aside is-placed-left is-expanded h-screen overflow-y-auto overflow-x-hidden">
    @php($admin = auth()->guard('admin')->user())
    <div class="aside-tools">
        <div>
            <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center px-4 py-2 bg-black rounded-lg">
                <span class="text-white text-lg font-medium">MJS</span>
                <span class="text-green-400 text-lg font-black ml-1">Organic</span>
            </a>
        </div>
    </div>
    <div class="menu is-menu-main">
        <p class="menu-label">General</p>
        <ul class="menu-list">
            @if($admin?->hasPermission('dashboard.view'))
            <li class="active">
                <a href="{{ route('admin.dashboard') }}">
                    <span class="icon"><i class="mdi mdi-desktop-mac"></i></span>
                    <span class="menu-item-label">Dashboard</span>
                </a>
            </li>
            @endif
        </ul>

        @if($admin?->hasPermission('products.manage'))
        <p class="menu-label">Product Management</p>
        <ul class="menu-list">
            <li>
                <a class="dropdown">
                    <span class="icon"><i class="mdi mdi-package-variant-closed"></i></span>
                    <span class="menu-item-label">Product</span>
                    <span class="icon"><i class="mdi mdi-plus"></i></span>
                </a>
                <ul>
                    <li><a href="{{ route('admin.products.index') }}"><span>Products</span></a></li>
                    <li><a href="{{ route('admin.categories.index') }}"><span>Categories</span></a></li>
                    <li><a href="{{ route('admin.product-stocks.index') }}"><span>Products Stock</span></a></li>
                    <li><a href="{{ route('admin.product-commissions.index') }}"><span>Product Commissions</span></a></li>
                </ul>
            </li>
        </ul>
        @endif

        @if($admin?->hasPermission('orders.manage'))
        <p class="menu-label">Orders</p>
        <ul class="menu-list">
            <li><a href="{{ route('admin.orders.index') }}"><span class="icon"><i class="mdi mdi-cart-outline"></i></span><span class="menu-item-label">Orders</span></a></li>
        </ul>
        @endif

        @if($admin?->hasPermission('users.manage'))
        <p class="menu-label">Users Managment</p>
        <ul class="menu-list">
            <li><a href="{{ route('admin.users.index') }}" class="has-icon"><span class="icon"><i class="mdi mdi-account-group-outline"></i></span><span class="menu-item-label">Users list</span></a></li>
        </ul>
        @endif

        @if($admin?->hasPermission('affiliates.manage'))
        <p class="menu-label">Affiliate Managment</p>
        <ul class="menu-list">
            <li><a href="{{ route('admin.affiliates.index') }}" class="has-icon"><span class="icon"><i class="mdi mdi-account-star-outline"></i></span><span class="menu-item-label">Affiliates</span></a></li>
            <li><a href="{{ route('admin.affiliate-withdraws.index') }}" class="has-icon"><span class="icon"><i class="mdi mdi-cash-refund"></i></span><span class="menu-item-label">Withdraw Requests</span></a></li>
        </ul>
        @endif

        @if($admin?->hasPermission('chats.manage'))
        <p class="menu-label">Chat Managment</p>
        <ul class="menu-list">
            <li><a href="{{ route('admin.chats.index') }}" class="has-icon"><span class="icon"><i class="mdi mdi-chat-processing-outline"></i></span><span class="menu-item-label">Chat</span></a></li>
        </ul>
        @endif

        @if($admin?->hasPermission('faqs.manage'))
        <p class="menu-label">FAQ Managment</p>
        <ul class="menu-list">
            <li><a href="{{ route('admin.faqs.index') }}" class="has-icon"><span class="icon"><i class="mdi mdi-frequently-asked-questions"></i></span><span class="menu-item-label">FAQ</span></a></li>
        </ul>
        @endif

        @if($admin?->hasPermission('settings.manage'))
        <p class="menu-label">Site Settings</p>
        <ul class="menu-list">
            <li>
                <a class="dropdown">
                    <span class="icon"><i class="mdi mdi-cog-outline"></i></span>
                    <span class="menu-item-label">Configuration</span>
                    <span class="icon"><i class="mdi mdi-plus"></i></span>
                </a>
                <ul>
                    <li><a href="{{ route('admin.site-settings.general') }}"><span>General Settings</span></a></li>
                    <li><a href="{{ route('admin.site-settings.menus') }}"><span>Header / Menu</span></a></li>
                    <li><a href="{{ route('admin.site-settings.footer') }}"><span>Footer Settings</span></a></li>
                    <li><a href="{{ route('admin.site-settings.pages.index') }}"><span>Page Settings</span></a></li>
                    <li><a href="{{ route('admin.seo-settings.index') }}"><span>SEO Settings</span></a></li>
                    <li><a href="{{ route('admin.sms-settings.index') }}"><span>SMS Marketing</span></a></li>
                    <li><a href="{{ route('admin.fb-settings.index') }}"><span>FB Settings</span></a></li>
                    <li><a href="{{ route('admin.ai-settings.index') }}"><span>AI Settings</span></a></li>
                    <li><a href="{{ route('admin.steadfast.index') }}"><span>Courier Settings</span></a></li>
                    <li><a href="{{ route('admin.delivery-charge.index') }}"><span>Delivery Area</span></a></li>
                </ul>
            </li>
        </ul>
        @endif

        @if($admin?->hasPermission('access-control.manage'))
        <p class="menu-label">Access Control</p>
        <ul class="menu-list">
            <li><a href="{{ route('admin.roles.index') }}" class="has-icon"><span class="icon"><i class="mdi mdi-shield-account"></i></span><span class="menu-item-label">Roles</span></a></li>
            <li><a href="{{ route('admin.permissions.index') }}" class="has-icon"><span class="icon"><i class="mdi mdi-key-chain"></i></span><span class="menu-item-label">Permissions</span></a></li>
            <li><a href="{{ route('admin.admins.index') }}" class="has-icon"><span class="icon"><i class="mdi mdi-account-cog-outline"></i></span><span class="menu-item-label">Admin Roles</span></a></li>
        </ul>
        @endif

        <p class="menu-label">About</p>
        <ul class="menu-list">
            <li><a href="#" class="has-icon"><span class="icon"><i class="mdi mdi-information-outline"></i></span><span class="menu-item-label">About</span></a></li>
            <li>
                <form method="POST" action="{{ route('admin.maintenance.clear') }}" onsubmit="return confirm('This will clear cache and current browser session data. Continue?');">
                    @csrf
                    <button type="submit" class="has-icon w-full flex items-center gap-3 rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-left text-red-700 hover:bg-red-100">
                        <span class="icon"><i class="mdi mdi-broom"></i></span>
                        <span class="menu-item-label">Clear App Data</span>
                    </button>
                </form>
            </li>
        </ul>
    </div>
</aside>
