<aside class="aside is-placed-left is-expanded">
    <div class="aside-tools">
        <div>
            <a href="{{ route('home') }}" target="_blank" rel="noopener noreferrer"
                class="inline-flex items-center px-4 py-2 bg-black rounded-lg">
                <span class="text-white text-lg font-medium">MJS</span>
                <span class="text-green-400 text-lg font-black ml-1">Organic</span>
            </a>
        </div>
    </div>
    <div class="menu is-menu-main">
        <p class="menu-label">General</p>
        <ul class="menu-list">
            <li class="active">
                <a href="{{ route('admin.dashboard') }}">
                    <span class="icon"><i class="mdi mdi-desktop-mac"></i></span>
                    <span class="menu-item-label">Dashboard</span>
                </a>
            </li>
        </ul>
        <p class="menu-label">Product Management</p>
        <ul class="menu-list">
            <li>
                <a class="dropdown">
                    <span class="icon"><i class="mdi mdi-view-list"></i></span>
                    <span class="menu-item-label">Product</span>
                    <span class="icon"><i class="mdi mdi-plus"></i></span>
                </a>
                <ul>
                    <li>
                        <a href="{{ route('admin.products.index') }}">
                            <span>Products</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.categories.index') }}">
                            <span>Categories</span>
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('admin.product-stocks.index') }}">
                            <span>Products Stock</span>
                        </a>
                    </li>
                </ul>
            </li>

            <li class="--set-active-tables-html">
                <a href="tables.html">
                    <span class="icon"><i class="mdi mdi-table"></i></span>
                    <span class="menu-item-label">Tables</span>
                </a>
            </li>
            <li class="--set-active-forms-html">
                <a href="forms.html">
                    <span class="icon"><i class="mdi mdi-square-edit-outline"></i></span>
                    <span class="menu-item-label">Forms</span>
                </a>
            </li>
            <li class="--set-active-profile-html">
                <a href="profile.html">
                    <span class="icon"><i class="mdi mdi-account-circle"></i></span>
                    <span class="menu-item-label">Profile</span>
                </a>
            </li>
            <li>
                <a href="login.html">
                    <span class="icon"><i class="mdi mdi-lock"></i></span>
                    <span class="menu-item-label">Login</span>
                </a>
            </li>
        </ul>
        <p class="menu-label">About</p>
        <ul class="menu-list">
            <li>
                <a href="https://justboil.me/tailwind-admin-templates/free-dashboard/" class="has-icon">
                    <span class="icon"><i class="mdi mdi-help-circle"></i></span>
                    <span class="menu-item-label">About</span>
                </a>
            </li>
            <li>
                <a href="https://github.com/justboil/admin-one-tailwind" class="has-icon">
                    <span class="icon"><i class="mdi mdi-github-circle"></i></span>
                    <span class="menu-item-label">GitHub</span>
                </a>
            </li>
        </ul>
    </div>
</aside>
