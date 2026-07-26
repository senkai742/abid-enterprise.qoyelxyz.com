<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->


    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="image">
                <img src="{{ auth()->user()->getAvatar() }}" class="img-circle elevation-2" alt="User Image">
            </div>
            <div class="info">
                <a href="#" class="d-block">{{ auth()->user()->getFullname() }}</a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                <li class="nav-item has-treeview">
                    <a href="{{route('admin.dashboard')}}" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>{{ __('dashboard.title') }}</p>
                    </a>
                </li>

                <li class="nav-item has-treeview">
                    <a href="{{ route('admin.cart.index') }}" class="nav-link {{ activeSegment('cart') }}">
                        <i class="nav-icon fas fa-shopping-cart"></i>
                        <p>{{ __('POS') }}</p>
                    </a>
                </li>

                <li class="nav-item has-treeview">
                    <a href="{{ route('admin.sales.index') }}" class="nav-link {{ activeSegment('orders') }}">
                        <i class="nav-icon fas fa-receipt"></i>
                        <p>Sales list</p>
                    </a>
                </li>

                <li class="nav-item has-treeview">
                    <a href="{{ route('admin.purchase.index') }}" class="nav-link {{ activeSegment('purchase') }}">
                        <i class="nav-icon fas fa-truck"></i>
                        <p>{{ __('Purchase') }}</p>
                    </a>
                </li>


                <li class="nav-item item-separator">
                     <hr style="border-top:1px solid #666">
                </li>

                <li class="nav-item has-treeview">
                    <a href="{{ route('admin.salesreturn.index') }}" class="nav-link {{ activeSegment('sales-return') }}">
                        <i class="nav-icon fas fa-undo"></i>
                        <p>{{ __('Sales Return') }}</p>
                    </a>
                </li>

                <li class="nav-item has-treeview">
                    <a href="{{ route('admin.purchasereturn.index') }}" class="nav-link {{ activeSegment('purchase-return') }}">
                        <i class="nav-icon fas fa-reply"></i>
                        <p>{{ __('Purchase Return') }}</p>
                    </a>
                </li>

                <li class="nav-item has-treeview">
                    <a href="{{ route('admin.damage.index') }}" class="nav-link {{ activeSegment('damage') }}">
                        <i class="nav-icon fas fa-exclamation-triangle"></i>
                        <p>{{ __('Damage') }}</p>
                    </a>
                </li>

                 <li class="nav-item item-separator">
                     <hr style="border-top:1px solid #666">
                </li>

                 <li class="nav-item has-treeview">
                    <a href="{{ route('admin.expense.index') }}" class="nav-link {{ activeSegment('expense') }}">
                        <i class="nav-icon fas fa-chart-line"></i>
                        <p>Report & Expenses</p>
                    </a>
                </li>

                <li class="nav-item item-separator">
                     <hr style="border-top:1px solid #666">
                </li>


                <li class="nav-item has-treeview">
                    <a href="{{ route('admin.products.index') }}" class="nav-link {{ activeSegment('products') }}">
                        <i class="nav-icon fas fa-boxes"></i>
                        <p>{{ __('product.title') }}</p>
                    </a>
                </li>

                <li class="nav-item has-treeview">
                    <a href="{{ route('admin.branch-transfer.index') }}" class="nav-link">
                        <i class="nav-icon fas fa-exchange-alt"></i>
                        <p>Branch Transfer</p>
                    </a>
                </li>

                <li class="nav-item has-treeview">
                    <a href="{{ route('admin.customers.index') }}" class="nav-link {{ activeSegment('customers') }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>{{ __('customer.title') }}</p>
                    </a>
                </li>
                <li class="nav-item has-treeview">
                    <a href="{{ route('admin.suppliers.index') }}" class="nav-link {{ activeSegment('supplier') }}">
                        <i class="nav-icon fas fa-handshake"></i>
                        <p>{{ __('Supplier') }}</p>
                    </a>
                </li>
                <li class="nav-item has-treeview">
                    <a href="{{ route('admin.settings.index') }}" class="nav-link {{ activeSegment('settings') }}">
                        <i class="nav-icon fas fa-cogs"></i>
                        <p>{{ __('settings.title') }}</p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="#" class="nav-link" onclick="document.getElementById('logout-form').submit()">
                        <i class="nav-icon fas fa-sign-out-alt"></i>
                        <p>{{ __('common.Logout') }}</p>
                        <form action="{{route('logout')}}" method="POST" id="logout-form">
                            @csrf
                        </form>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
