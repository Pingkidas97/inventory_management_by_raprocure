<!---header-part-->
<div class="project_header" id="project_header">
    <header class="Project_top_header">
        <div class="container-fluid">
            <div class="top_head row align-items-center">
                <div class="col-4 col-md-1 col-lg-2 col-xl-4 top-head-left"></div>
                <div class="col-12 col-md-6 col-lg-4 col-xl-4 d-lg-block d-none top-head-middle">
                    <h4 class="text-center">Welcome to Raprocure!</h4>
                </div>
                <div class="col-12 col-md-12 col-lg-6 col-xl-4 top-head-right">
                    <p class="text-white show_bothNo">
                        Helpline No.: 9088880077 / 9088844477
                    </p>
                    <h5>Raprocure Support</h5>
                </div>
            </div>
        </div>
    </header>
    <div class="project_bottom_header">
        <div class="container-fluid">
            <div class="cust_container">
                <div class="row btm_heada">
                    <div class="col-lg-2 col-md-6 col-sm-5 col-5 navbar-logo bottom-header-left">
                        <a class="logo-brand p-0" href="#">
                            <img alt=" " class="brand-logo-img" src="{{ asset('public/assets/superadmin/images/rfq-logo.png') }}" />
                        </a>
                    </div>
                    <div class="col-lg-8 col-md-1 col-sm-1 col-1 bottom-header-center d-lg-none">
                        <a href="javascript:void(0)" onclick="openNav()"><i class="bi bi-list"></i></a>
                    </div>
                     <div class="col-lg-2 col-md-5 col-sm-6 col-6 bottom-header-end">
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
                    <a href="javascript:void(0)" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Support">
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