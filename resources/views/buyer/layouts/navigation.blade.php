<header class="Project_top_header">
    <div class="container-fluid">
        <div class="cust_container">
            <div class="top_head row align-items-center">
                <div class="col-4 col-md-1 col-lg-2 col-xl-4 top-head-left">
                    <h5 title="{{session('legal_name')}}">{{ (strlen(session('legal_name')) > 50) ? substr(session('legal_name'), 0, 50) . '...' : session('legal_name') }}</h5>
                </div>
                <div class="col-12 col-md-6 col-lg-4 col-xl-4 d-lg-block d-none top-head-middle">
                    <h4 class="text-center">Welcome to Raprocure!</h4>
                </div>
                <div class="col-12 col-md-12 col-lg-6 col-xl-4 top-head-right">
                    <p class="text-white show_bothNo">
                        Helpline No.: 9088880077 / 9088844477
                    </p>
                    <div class="dropdown user">
                        <a class="dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" title="{{Auth::user()->name}}">
                            <span class="bi bi-person-fill" aria-hidden="true"></span>
                            {{ (strlen(Auth::user()->name) > 10) ? substr(Auth::user()->name, 0, 10) . '...' : Auth::user()->name }}
                        </a>
                        <ul class="dropdown-menu user_logout">
                            <li>
                                <a class="dropdown-item" href="{{ route('user.logout') }}">Logout</a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>
<div class="project_bottom_header">
    <div class="container-fluid">
        <div class="cust_container">
            <div class="row btm_heada">
                <div class="col-lg-2 col-md-6 col-sm-6 col-6 navbar-logo bottom-header-left">
                    <div class="d-flex align-items-center">
                        <div class="toggole mt-2">
                            <a href="javascript:void(0)" onclick="openNav()">
                                <span class="visually-hidden-focusable">Menu</span>
                                <span class="bi bi-list font-size-32 fw-bold" aria-hidden="true"></span>
                            </a>
                        </div>
                        <a class="logo-brand p-0" href="{{ route("buyer.dashboard") }}">
                            <img alt="raProcure" class="brand-logo-img" src="{{ asset('public/assets/images/rfq-logo.png') }}" />
                        </a>
                    </div>
                </div>
                <div class="col-lg-8 col-md-12 col-sm-12 col-12 bottom-header-center">
                    @if(Auth::user()->is_profile_verified==1)
                    <div class="category_division" id="search-by-division">
                        <div onclick="setSearch(event)" class="d-flex justify-content-between align-items-center">
                            <span>Search By Division </span>
                            <span class="bi bi-chevron-down" aria-hidden="true"></span>
                        </div>
                        <div class="category-by-division" id="category_by_division"></div>
                    </div>
                    <div class="product-serach-box">
                        <form class="d-flex searchBar">
                            <span class="bi bi-search me-2"></span>
                            <input type="search" class="" id="product-search" placeholder="Search Product">
                            <button class="btn p-0" type="button" data-bs-toggle="tooltip" data-bs-placement="bottom" aria-label="Start Voice Search" data-bs-original-title="Start Voice Search">
                                <span class="bi bi-mic-fill font-size-18"></span>
                            </button>
                        </form>
                        <ul class="search_text_box" id="product-search-list" style="display: none;"></ul>
                    </div>
                    @endif
                </div>
                <div class="col-lg-2 col-md-6 col-sm-6 col-6 bottom-header-end">
                    @if(Auth::user()->is_profile_verified==1)
                    <ul>
                        <li class="notify-section">
                            <a href="javascript:void(0)" class="btn-link" onclick="setNotify(event)" id="notifyButton"
                                data-bs-toggle="tooltip" data-bs-placement="bottom" title="Notification">
                            <i class="bi bi-bell font-size-22" aria-hidden="true"></i>
                            <span class="notification-number">7</span>
                            </a>
                            <div class="bell_messages" id="Allnotification_messages">
                                <div class="message_wrap">
                                    <div class="message-wrapper notification-bg-blue">
                                        <div class="message-detail">
                                            <a href="javascript:void(0)">
                                                <div class="message-head-line">
                                                    <div class="person_name">
                                                        <span>A KUMAR</span>
                                                    </div>
                                                    <p class="message-body-line">
                                                        26 Mar, 2025 05:12 PM
                                                    </p>
                                                </div>
                                                <p class="message-body-line">
                                                    'A KUMAR' has responded to your RFQ No.
                                                    RATB-25-00046. You can check their quote here
                                                </p>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="message-wrapper notification-bg-pink">
                                        <div class="message-detail">
                                            <a>
                                                <div class="message-head-line">
                                                    <div class="person_name">
                                                        <span>A KUMAR</span>
                                                    </div>
                                                    <p class="message-body-line">
                                                        26 Mar, 2025 05:12 PM
                                                    </p>
                                                </div>
                                                <p class="message-body-line">
                                                    'A KUMAR' has responded to your RFQ No.
                                                    RATB-25-00046. You can check their quote here
                                                </p>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="message-wrapper notification-bg-yellow">
                                        <div class="message-detail">
                                            <a>
                                                <div class="message-head-line">
                                                    <div class="person_name">
                                                        <span>TEST AMIT VENDOR</span>
                                                    </div>
                                                    <p class="message-body-line">
                                                        26 Mar, 2025 04:35 PM
                                                    </p>
                                                </div>
                                                <p class="message-body-line">
                                                    'TEST AMIT VENDOR' has responded to your RFQ No.
                                                    RATB-25-00046. You can check their quote here
                                                </p>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="message-wrapper notification-bg-green">
                                        <div class="message-detail">
                                            <a>
                                                <div class="message-head-line">
                                                    <div class="person_name">
                                                        <span>A KUMAR</span>
                                                    </div>
                                                    <p class="message-body-line">
                                                        26 Mar, 2025 04:35 PM
                                                    </p>
                                                </div>
                                                <p class="message-body-line">
                                                    'A KUMAR' has responded to your RFQ No.
                                                    RATB-25-00046. You can check their quote here
                                                </p>
                                            </a>
                                        </div>
                                    </div>
                                    <a href="javascript:void(0)">View All Notification</a>
                                </div>
                            </div>
                        </li>
                        <li class="circle_info">
                            <a href="javascript:void(0)" class="btn-link" data-bs-toggle="tooltip" data-bs-placement="bottom"
                                title="Help">
                            <i class="bi bi-question-circle font-size-22" aria-hidden="true"></i>
                            </a>
                        </li>
                    </ul>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>