<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta name="description" content="" />
    <meta name="keywords" content="" />
    <meta name="author" content="" />
    <meta property="og:image:size" content="300" />
    <link rel="shortcut icon" href="{{ asset('public/assets/superadmin/favicon/raprocure-fevicon.ico') }}" type="image/x-icon"/>
    <title>@yield('title', 'Raprocure')</title>
    <link href="{{ asset('public/assets/superadmin/bootstrap/css/bootstrap.min.css') }}" rel="stylesheet" />
    <!---css-->
    <link href="{{ asset('public/assets/superadmin/css/style.css') }}" rel="stylesheet" />
    <link href="{{ asset('public/assets/superadmin/css/layout.css') }}" rel="stylesheet" />
    <link href="{{ asset('public/assets/superadmin/css/custom.css') }}" rel="stylesheet" />
    <link href="{{ asset('public/assets/superadmin/css/dashboard.css') }}" rel="stylesheet" />
    <!-- jQuery -->
    <script src="{{ asset('public/assets/jQuery/jquery-3.6.0.min.js') }}"></script>
    <!-- Toastr CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    @yield('css')
  </head>

  <body>
    <div class="project_header" id="project_header">
        <header class="P_header">
            <div class="container-fluid">
                <div class="top_head row align-items-center">
                    <div class="col-4 col-md-1 col-lg-2 col-xl-4 top-section-left"></div>
                    <div class="col-12 col-md-6 col-lg-4 col-xl-4 d-lg-block d-none top-section-middle">
                        <h4 class="text-center">Welcome to Raprocure!</h4>
                    </div>
                    <div class="col-12 col-md-12 col-lg-6 col-xl-4 top-section-right">
                        <p class="text-white show_bothNo">
                            Helpline No.: 9088880077 / 9088844477
                        </p>
                        <h5>Raprocure Support</h5>
                    </div>
                </div>
            </div>
        </header>
        <div class="header" id="myHeader">
            <div class="container-fluid">
                <div class="cust_container">
                    <div class="row btm_heada">
                        <div class="col-lg-2 col-md-6 col-sm-5 col-5 navbar-header header-bottom-left">
                            <a class="navbar-brand p-0" href="#">
                            <img alt="logo" class="header_img_final" src="{{ asset('public/assets/superadmin/images/rfq-logo.png') }}"/>
                            </a>
                        </div>
                        <div class="col-lg-8 col-md-1 col-sm-1 col-1 header-bottom-middle d-lg-none">
                            <a href="javascript:void(0);" onclick="openNav()"><i class="fa-solid fa-bars-staggered"></i></a>
                        </div>
                        <div class="col-lg-2 col-md-5 col-sm-6 col-6 globle-header-icons header-bottom-right">
                            <ul>
                                <li class="notify-section">
                                    <a href="javascript:void(0)" onclick="setNotify(event)" id="notifyButton" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Notification">
                                        <i class="bi bi-bell"></i>
                                        <span class="notification-number">1</span>
                                    </a>
                                    <div class="bell_messages" id="Allnotification_messages">
                                    <div class="message_wrap">
                                        <div class="message-wrapper Nblue">
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
                                        <div class="message-wrapper Npink">
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
                                        <div class="message-wrapper Nyellow">
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
                                        <div class="message-wrapper Ngreen">
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

                                <li class="notify-section">
                                    <a href="{{ route('admin.help_support.index') }}" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Support">
                                    <i class="bi bi-question-circle"></i>
                                    </a>
                                </li>
                                <li class="bottom_user">
                                    <a href="javascript:void(0)" class="d-flex align-items-center userImg" onclick="setLogout(event)" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Logout">
                                    <i class="bi bi-person-circle"></i>
                                    </a>
                                    <div class="user_logout" id="user_logout">
                                    <form method="POST" action="{{ route('admin.logout') }}">
                                        @csrf
                                        <button type="submit" style="width: 100px;background: white;border: none;"><i class="fa-solid fa-arrow-right-from-bracket"></i>
                                        Logout</button>
                                    </form>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="dashboard_page">
        <div class="sidebar" id="mySidebar">
            <div class="pg-sideer">
                <a href="javascript:void(0);" onclick="closeNav()" class="close-icon"><i class="bi bi-x-circle"></i></a>
                <div class="menu accordion" id="accordionExample">
                    <div class="dash">
                        <a href="{{ route('admin.dashboard') }}" class="sash"><i class="bi bi-gem  sidebar-bi"></i>
                            <span class="nav_text">Dashboard</span></a>
                    </div>
                    <div>
                        <a href="{{ route('admin.divisions.index') }}" class="sash clr"><i class="bi bi-speedometer2 sidebar-bi"></i>
                            <span class="nav_text">Product Directory</span></a>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingOne">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false"  aria-controls="collapseOne" >
                                <i class="bi bi-layers sidebar-bi"></i><span class="nav_text"> Manage products</span>
                            </button>
                        </h2>
                        <div id="collapseOne" class="accordion-collapse collapse" aria-labelledby="headingOne" data-bs-parent="#accordionExample" >
                            <div class="accordion-body">
                                <ul class="accor-submenu">
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
                        <a href="{{ route('admin.buyer.index') }}" class="sash clr"><i class="bi bi-bag-check sidebar-bi"></i>
                            <span class="nav_text">Buyer Module</span></a>
                    </div>

                    <div>
                        <a href="{{ route('admin.vendor.index') }}" class="sash clr"><i class="bi bi-person-check sidebar-bi"></i><span class="nav_text"> Vendor Module</span></a>
                    </div>
                    <div>
                        <a href="{{ route('admin.advertisement.index') }}" class="sash clr">
                            <i class="bi bi-megaphone me-2"></i><span class="nav_text">Advertisement/Marketing</span>
                        </a>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFive">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
                                <i class="bi bi-person-add sidebar-bi"></i>
                                <span class="nav_text">Accounts Module</span>
                            </button>
                        </h2>
                        <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <ul class="accor-submenu">
                                    <li><a href="{{ route('admin.accounts.buyer') }}">Buyer’s Accounts</a></li>
                                    <li><a href="{{ route('admin.accounts.vendor') }}">Vendor’s Accounts</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div>
                        <a href="#" class="sash clr">
                            <i class="bi bi-layout-wtf me-2"></i>
                            <span class="nav_text">Plan Module </span>
                        </a>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingThree">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree" >
                                <i class="bi bi-file-earmark-text sidebar-bi"></i><span class="nav_text">Reports</span>
                            </button>
                        </h2>
                        <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionExample" >
                            <div class="accordion-body">
                                <ul class="accor-submenu" aria-labelledby="headingThree" data-bs-parent="#accordionExample" >
                                    <li><a href="{{route('admin.reports.product-division-category')}}">Products Division & Category Wise</a></li>
                                    <li><a href="{{ route('admin.vendor-activity-report.index') }}">Vendor Activity Reports</a></li>
                                    <li><a href="{{route('admin.reports.buyer-activity')}}">Buyer Activity Reports</a></li>
                                    <li><a href="{{ route('admin.vendor-disabled-product-report.index') }}">Vendor Disabled Products</a></li>
                                    <li><a href="{{ route('admin.rfq-summary-report.index') }}">RFQs Summary</a></li>
                                    <li><a href="{{ route('admin.reports.auction-rfqs-summary') }}">Auction RFQs Summary</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingTwo">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo" >
                                <i class="bi bi-person-lines-fill sidebar-bi"></i><span class="nav_text">User Management</span>
                            </button>
                        </h2>
                        <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionExample" >
                            <div class="accordion-body">
                                <ul class="accor-submenu" aria-labelledby="headingTwo" data-bs-parent="#accordionExample" >
                                    <li><a href="{{ route('admin.users.index') }}">Admin User</a></li>
                                    <li><a href="{{ route('admin.user-roles.index') }}">Manage role</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div>
                        <a href="{{ route('admin.help_support.index') }}" class="sash clr" ><i class="bi bi-question-circle sidebar-bi"></i>
                            <span class="nav_text"> Help and Support</span>
                        </a>
                    </div>
                    <div>
                        <a href="{{ route('admin.password.change') }}" class="sash clr" ><i class="bi bi-lock sidebar-bi"></i><span class="nav_text"> Change Password </span></a>
                    </div>

                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingFour">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                <i class="bi bi-chat-dots sidebar-bi"></i>
                                <span class="nav_text"> Message</span>
                            </button>
                        </h2>
                        <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionExample">
                            <div class="accordion-body">
                                <ul class="accor-submenu" aria-labelledby="headingThree" data-bs-parent="#accordionExample">
                                    <li><a href="#">Internal (<span>0</span>)</a></li>
                                    <li><a href="#">Buyer (<span>0</span>)</a></li>
                                    <li><a href="#">Vendor (<span>0</span>)</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div>
                        <a href="#" class="sash clr"><i class="bi bi-info-circle sidebar-bi"></i><span class="nav_text"> Buyer Query</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <div class="main" id="main">
            <div class="container-fluid">
                @yield('content')
            </div>
        </div>
    </div>


    <!---bootsrap-->
    <script src="{{ asset('public/assets/superadmin/bootstrap/js/bootstrap.bundle.js') }}"></script>
    <!---local-js-->
    <script src="{{ asset('public/assets/superadmin/js/common.js') }}"></script>
    <script>
      function limitCharacters(inputField, maxLength) {
          if (inputField.value.length > maxLength) {
              toastr.error(`Character limit exceeded! Maximum ${maxLength} characters allowed.`);
              inputField.value = inputField.value.substring(0, maxLength);
          }
      }
    </script>
    @yield('scripts')
  </body>
</html>
