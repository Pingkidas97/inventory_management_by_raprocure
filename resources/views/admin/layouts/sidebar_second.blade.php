<div class="sidebar onClickMenuSidebar" id="mySidebar">
    <div class="page-slider">
        <a href="javascript:void(0)" onclick="closeNav()" class="close-icon"><i class="bi bi-x-lg"></i></a>
        <div class="sidebar-menu-list accordion" id="sidebarAccordion">
            <div class="dashboard_text">
                <a href="{{ route('admin.dashboard') }}" class="menu-text-colour"><i class="bi bi-gem  sidebar-bi"></i>
                    <span class="nav_text">Dashboard</span></a>
            </div>

            <div>
                <a href="{{ route('admin.divisions.index') }}" class="menu-text-colour submenu-list"><i class="bi bi-speedometer2 sidebar-bi"></i>
                    <span class="nav_text">Product Directory</span></a>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                        <i class="bi bi-layers sidebar-bi"></i><span class="nav_text"> Manage products</span>
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne"
                    data-bs-parent="#sidebarAccordion">
                    <div class="accordion-body">
                        <ul class="accordian-submenu">
                            <li><a href="{{ route('admin.verified-products.index') }}">All Verified Products</a></li>
                            <li><a href="{{ route('admin.product-approvals.index') }}">Products for Approval</a></li>
                            <li><a href="{{ route('admin.new-products.index') }}">New Product Request</a></li>
                            <li><a href="{{ route('admin.edit-products.index') }}">Edit Product</a></li>
                            <li><a href="{{ route('admin.bulk-products.index') }}">Products for Approval Bulk</a></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div>
                <a href="{{ route('admin.buyer.index') }}" class="menu-text-colour submenu-list"><i class="bi bi-bag sidebar-bi"></i>
                    <span class="nav_text">Buyer Module</span></a>
            </div>

            <div>
                <a href="{{ route('admin.vendor.index') }}" class="menu-text-colour submenu-list"><i class="bi bi-shop-window sidebar-bi"></i>
                    <span class="nav_text"> Vendor Module</span></a>
            </div>

            <div>
                <a href="{{ route('admin.advertisement.index') }}" class="menu-text-colour submenu-list"><i class="bi bi-megaphone sidebar-bi"></i>
                    <span class="nav_text">Advertisement/Marketing</span>
                </a>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingFive">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                        <i class="bi bi-person-plus sidebar-bi"></i>
                        <span class="nav_text">Accounts Module</span>
                    </button>
                </h2>
                <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive"
                    data-bs-parent="#sidebarAccordion">
                    <div class="accordion-body">
                        <ul class="accordian-submenu">
                            <li><a href="{{ route('admin.accounts.buyer') }}">Buyer's Accounts</a></li>
                            <li><a href="{{ route('admin.accounts.vendor') }}">Vendor's Accounts</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div>
                <a href="{{ route('admin.plan.index') }}" class="menu-text-colour submenu-list"><i class="bi bi-columns sidebar-bi"></i>
                    <span class="nav_text">Plan Module </span></a>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingThree">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                        <i class="bi bi-file-earmark-text sidebar-bi"></i><span class="nav_text">Reports</span>
                    </button>
                </h2>
                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree"
                    data-bs-parent="#sidebarAccordion">
                    <div class="accordion-body">
                        <ul class="accordian-submenu" aria-labelledby="headingThree" data-bs-parent="#sidebarAccordion">
                            <li><a href="{{route('admin.reports.product-division-category')}}">Products Division & Category Wise</a></li>
                            <li><a href="{{route('admin.reports.buyer-activity')}}">Buyer Activity Reports</a></li>
                            <li><a href="{{ route('admin.vendor-activity-report.index') }}">Vendor Activity Reports</a></li>
                            <li><a href="{{ route('admin.vendor-disabled-product-report.index') }}">Vendor Disabled Products</a></li>
                            <li><a href="{{ route('admin.rfq-summary-report.index') }}">RFQs Summary</a></li>
                            <li><a href="{{ route('admin.reports.auction-rfqs-summary') }}">Auction RFQs Summary</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingTwo">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        <i class="bi bi-people"></i><span class="nav_text">User Management</span>
                    </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo"
                    data-bs-parent="#sidebarAccordion">
                    <div class="accordion-body">
                        <ul class="accordian-submenu" aria-labelledby="headingTwo" data-bs-parent="#sidebarAccordion">
                            <li><a href="{{ route('admin.users.index') }}">Admin User</a></li>
                            <li><a href="{{ route('admin.user-roles.index') }}">Manage role</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div>
                <a href="{{ route('admin.help_support.index') }}" class="menu-text-colour submenu-list"><i class="bi bi-question-circle sidebar-bi"></i>
                    <span class="nav_text"> Help and Support</span>
                </a>
            </div>
            <div>
                <a href="{{ route('admin.password.change') }}" class="menu-text-colour submenu-list"><i class="bi bi-lock"></i><span class="nav_text">
                        Change Password </span></a>
            </div>

            <div class="accordion-item">
                <h2 class="accordion-header" id="headingFour">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                        <i class="bi bi-chat-left-text sidebar-bi"></i>
                        <span class="nav_text"> Message</span>
                    </button>
                </h2>
                <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour"
                    data-bs-parent="#sidebarAccordion">
                    <div class="accordion-body">
                        <ul class="accordian-submenu" aria-labelledby="headingThree" data-bs-parent="#sidebarAccordion">
                            <li><a href="#">Internal (<span>0</span>)</a></li>
                            <li><a href="#">Buyer (<span>0</span>)</a></li>
                            <li><a href="#">Vendor (<span>0</span>)</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div>
                <a href="#" class="menu-text-colour submenu-list"><i class="bi bi-info-circle sidebar-bi"></i><span
                        class="nav_text"> Buyer Query</span>
                </a>
            </div>
        </div>
    </div>
</div>