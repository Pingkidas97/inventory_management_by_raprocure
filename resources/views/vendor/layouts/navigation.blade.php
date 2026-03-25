<!---Header part-->
<header class="sticky-top">
  <div class="d-flex header-top bg-graident align-item-center justify-content-between text-white font-size-12 gap-30">
    <div class="header-top-left text-uppercase vendor-name d-none d-lg-block">
      RONIT VENDOR PROFILE COMPANY QWE
    </div>
    <div class="header-top-middle welcome d-none d-lg-block">Welcome to Raprocure!</div>
    <div class="header-top-right">
      <div class="helpline">
        <span class="d-none d-md-block">Helpline No.: 9088880077 / 9088844477</span>
        <span class="d-md-none">Call on 9088880077</span>
      </div>
      <div class="user-name">WERWERWER</div>
    </div>
  </div>
  <div class="header-bottom d-flex align-items-center justify-content-between gap-30">
    <div class="d-flex align-items-center">
      <div class="toggle-menu mt-2 mr-2">
        <button class="ra-btn ra-btn-link d-lg-none" onclick="openNav()">
          <span class="visually-hidden-focusable">Menu</span>
          <span class="toggle-menu-icon">
            <span class="line"></span>
            <span class="line"></span>
            <span class="line"></span>
          </span>
        </button>
      </div>
      <div class="brand">
        <a class="logo-brand p-0" href="javascript:void(0)">
          <img alt="Raprocure logo" class="brand-logo-img" src="{{ asset('public/assets/vendor/images/rfq-logo.png') }}">
        </a>
      </div>
    </div>

    <div class="header-nav-right">
      <ul class="d-flex flex-row align-items-center">
        <li>
          <button type="button" aria-label="Mini Webpage" class="ra-btn ra-btn-link font-size-24 custom-tooltip"
            data-tooltip="Mini Webpage" href="" target="blank">
            <span class="bi bi-house" aria-hidden="true"></span>
          </button>
        </li>
        <li>
          <div class="notify-section">
            <button type="button" class="ra-btn ra-btn-link font-size-24 custom-tooltip" aria-label="Notification"
              data-toggle="dropdown" aria-expanded="false" data-tooltip="Notification" onclick="setNotify(event)"
              id="notifyButton">
              <span class="bi bi-bell" aria-hidden="true"></span>
              <span class="notification-number">7</span>
            </button>
            <div class="bell-messages" id="Allnotification-messages">
              <div class="message-wrap">
                <div class="message-wrapper notification-bg-blue">
                  <div class="message-detail">
                    <a href="javascript:void(0)">
                      <div class="message-head-line">
                        <div class="person-name">
                          <span>RON PVT LTD</span>
                        </div>
                        <p class="message-body-line">
                          02 Jun, 2025 12:53 PM
                        </p>
                      </div>
                      <p class="message-body-line">
                        New RFQ has been received from RON PVT LTD. RFQ No. RONI-25-00038.
                      </p>
                    </a>
                  </div>
                </div>
                <div class="message-wrapper notification-bg-pink">
                  <div class="message-detail">
                    <a>
                      <div class="message-head-line">
                        <div class="person-name">
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
                        <div class="person-name">
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
                        <div class="person-name">
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
                <div class="text-center"><a href="{{route('vendor.notification.index')}}" class="view-notification">View All
                    Notification</a></div>
              </div>
            </div>
          </div>
        </li>
        <li>
          <a class="ra-btn ra-btn-link font-size-24 custom-tooltip" aria-label="Support"
            data-tooltip="Support" href="{{ route('vendor.help_support.index') }}">
            <span class="bi bi-question-circle" aria-hidden="true"></span>
        </a>
        </li>
        <li>
          <div class="dropdown">
            <button type="button" class="ra-btn ra-btn-link font-size-24 custom-tooltip dropdown-toggle"
              aria-label="Profile" data-tooltip="Profile" id="dropdownProfileButton" data-bs-toggle="dropdown"
              aria-expanded="false">
              <span class="bi bi-person" aria-hidden="true"></span>
            </button>
            <div class="dropdown-menu profile-dropdown-menu" aria-labelledby="dropdownProfileButton">
              <a class="dropdown-item" href="{{ route('user.logout') }}">
                  Logout 
              </a>
              <!-- <a class="dropdown-item" href="{{ route('logout') }}"
                 onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                  Logout 
              </a>
              <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                  @csrf
              </form> -->
            </div>
          </div>
        </li>
      </ul>
    </div>
  </div>
</header>