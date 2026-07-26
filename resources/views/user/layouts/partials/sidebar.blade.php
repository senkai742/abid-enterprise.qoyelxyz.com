<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-light-primary elevation-4" style="background-color:#fff8f4;box-shadow:none !important;border-right:1px solid #ccc">
    <!-- Brand Logo -->


    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user (optional) -->
        <div class="user-panel mt-0 pt-3 pb-2 pl-3 mb-3 d-flex bg-info row">
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
                    <a href="{{route('user.dashboard')}}" class="nav-link">
                        <i class="nav-icon fas fa-tachometer-alt"></i>
                        <p>{{ __('dashboard.title') }}</p>
                    </a>
                </li>

                <li class="nav-item has-treeview">
                    <a href="{{ route('user.cart.index') }}" class="nav-link {{ activeSegment('cart') }}">
                        <i class="nav-icon fas fa-cart-plus"></i>
                        <p>{{ __('POS') }}</p>
                    </a>
                </li>

                <li class="nav-item has-treeview">
                    <a href="{{ route('user.sales.index') }}" class="nav-link {{ activeSegment('orders') }}">
                        <i class="nav-icon fas fa-cart-plus"></i>
                        <p>Sales list</p>
                    </a>
                </li>

                <li class="nav-item has-treeview">
                    <a href="{{ route('user.salesreturn.index') }}" class="nav-link {{ activeSegment('sales-return') }}">
                        <i class="nav-icon fas fa-cart-plus"></i>
                        <p>{{ __('Sales Return') }}</p>
                    </a>
                </li>

                <li class="nav-item has-treeview">
                    <a href="{{ route('user.damage.index') }}" class="nav-link {{ activeSegment('damage') }}">
                        <i class="nav-icon fas fa-cart-plus"></i>
                        <p>{{ __('Damage') }}</p>
                    </a>
                </li>

                 <li class="nav-item item-separator">
                     <hr style="border-top:1px solid #666">
                </li>

                 <li class="nav-item has-treeview">
                    <a href="{{ route('user.expense.index') }}" class="nav-link {{ activeSegment('expense') }}">
                        <i class="nav-icon fas fa-cart-plus"></i>
                        <p>Expenses & Report</p>
                    </a>
                </li>

                <li class="nav-item item-separator">
                     <hr style="border-top:1px solid #666">
                </li>


                <li class="nav-item has-treeview">
                    <a href="{{ route('user.products.index') }}" class="nav-link {{ activeSegment('products') }}">
                        <i class="nav-icon fas fa-th-large"></i>
                        <p>{{ __('product.title') }}</p>
                    </a>
                </li>

                <li class="nav-item has-treeview">
                    <a href="{{ route('user.customers.index') }}" class="nav-link {{ activeSegment('customers') }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>{{ __('customer.title') }}</p>
                    </a>
                </li>
                <li class="nav-item has-treeview">
                    <a href="{{ route('user.suppliers.index') }}" class="nav-link {{ activeSegment('supplier') }}">
                        <i class="nav-icon fas fa-users"></i>
                        <p>{{ __('Supplier') }}</p>
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
