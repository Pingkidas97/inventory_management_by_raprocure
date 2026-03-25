<aside class="sidebar" id="mySidebar">
    <div class="page-slider">
        <a href="javascript:void(0)" onclick="closeNav()" class="close-icon"><i class="bi bi-x font-size-22"></i></a>
        @include('buyer.layouts.sidebar')
        {{-- <div class="sidebar-menu-list accordion custom-toggle-accordion" id="sidebarAccordion">
            <div class="active-menu">
                <a href="{{ route("buyer.dashboard") }}" class="menu-text-colour">
                <span class="sidebar_icon"><i class="bi bi-x-diamond-fill"></i></span>
                <span class="nav_text">Dashboard</span></a>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingOne">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne"
                        aria-expanded="false" aria-controls="collapseOne">
                    <span class="sidebar_icon"><i class="bi bi-journal-text"></i></span><span class="nav_text">RFQ</span>
                    </button>
                </h2>
                <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne">
                    <div class="accordion-body">
                        <ul class="accordian-submenu">
                            <li><a href="javascript:void(0)">Generate New RFQ</a></li>
                            <li><a href="javascript:void(0)">Generate Bulk RFQ</a></li>
                            <li><a href="javascript:void(0)">Active RFQs / CIS</a></li>
                            <li><a href="javascript:void(0)">Auction</a></li>
                            <li><a href="javascript:void(0)">Draft RFQ</a></li>
                            <li><a href="javascript:void(0)">Scheduled RFQ</a></li>
                            <li><a href="javascript:void(0)">Sent RFQ</a></li>
                            <li><a href="javascript:void(0)">Counter Offer</a></li>
                            <li><a href="javascript:void(0)"> Unapproved Orders </a></li>
                            <li><a href="javascript:void(0)"> Orders Confirmed </a></li>
                            <li><a href="javascript:void(0)"> PI's / Invoices </a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingFive">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                    <span class="sidebar_icon"><i class="bi bi-people"></i></span>
                    <span class="nav_text">Vendors</span>
                    </button>
                </h2>
                <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive">
                    <div class="accordion-body">
                        <ul class="accordian-submenu">
                            <li><a href="javascript:void(0)">Vendor Search</a></li>
                            <li><a href="javascript:void(0)">Add Your Vendor</a></li>
                            <li><a href="javascript:void(0)">Favourites</a></li>
                            <li><a href="javascript:void(0)">Blocked / Blacklisted</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingThree">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                    <span class="sidebar_icon"><i class="bi bi-clipboard2"></i></span>
                    <span class="nav_text">Reports</span>
                    </button>
                </h2>
                <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree">
                    <div class="accordion-body">
                        <ul class="accordian-submenu" aria-labelledby="headingThree">
                            <li><a href="javascript:void(0)">Total RFQ Created</a></li>
                            <li><a href="javascript:void(0)">Total RFQ Product Created</a></li>
                            <li><a href="javascript:void(0)">Products Ordered Details</a></li>
                            <li><a href="javascript:void(0)">Vendor Wise Activity</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div>
                <a href="javascript:void(0)" class="menu-text-colour submenu-list">
                <span class="sidebar_icon"><i class="bi bi-receipt"></i></span>
                <span class="nav_text">Inventory</span></a>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingTwo">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                    <span class="sidebar_icon"><i class="bi bi-gear"></i></span><span class="nav_text">Setting</span>
                    </button>
                </h2>
                <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo">
                    <div class="accordion-body">
                        <ul class="accordian-submenu" aria-labelledby="headingTwo">
                            <li><a href="javascript:void(0)">My Profile</a></li>
                            <li><a href="javascript:void(0)">Change Password</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingSix">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                        data-bs-target="#collapseSix" aria-expanded="false" aria-controls="collapseSix">
                    <span class="sidebar_icon"><i class="bi bi-person-workspace"></i></span>
                    <span class="nav_text"> User Management</span>
                    </button>
                </h2>
                <div id="collapseSix" class="accordion-collapse collapse" aria-labelledby="headingSix">
                    <div class="accordion-body">
                        <ul class="accordian-submenu" aria-labelledby="headingSix">
                            <li><a href="javascript:void(0)">Manage Users</a></li>
                            <li><a href="javascript:void(0)">Manage Role</a></li>
                        </ul>
                    </div>
                </div>
            </div>
            <div class="accordion-item">
                <h2 class="accordion-header" id="headingFour">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour"
                        aria-expanded="false" aria-controls="collapseFour">
                    <span class="sidebar_icon"><i class="bi bi-chat-dots"></i></span>
                    <span class="nav_text"> Message</span>
                    </button>
                </h2>
                <div id="collapseFour" class="accordion-collapse show" aria-labelledby="headingFour">
                    <div class="accordion-body">
                        <ul class="accordian-submenu pb-4" aria-labelledby="headingFour">
                            <li><a href="javascript:void(0)">Internal (<span>0</span>)</a></li>
                            <li><a href="javascript:void(0)">Vendors (<span>0</span>)</a></li>
                            <li><a href="javascript:void(0)">RaProcure(<span>0</span>)</a></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div> --}}
    </div>
</aside>