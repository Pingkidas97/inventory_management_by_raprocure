<div class="bg-white">
  <!---Sidebar-->
  <aside class="sidebar sidebar-dashboard-page" id="mySidebar">
    <div class="page-slider">
      <a href="javascript:void(0)" onclick="closeNav()" class="close-icon"><i class="bi bi-x font-size-22"></i></a>
      <div class="sidebar-menu-list accordion custom-toggle-accordion" id="sidebarAccordion">
        <div class="active-menu">
          <a href="{{ route('vendor.dashboard') }}" class="menu-text-colour">
            <span class="sidebar-icon"><span class="bi bi-x-diamond-fill" aria-hidden="true"></span></span>
            <span class="nav-text">Dashboard</span></a>
        </div>
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingOne">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne"
              aria-expanded="false" aria-controls="collapseOne">
              <span class="sidebar-icon"><span class="bi bi-journal-text" aria-hidden="true"></span></span><span
                class="nav-text">RFQ</span>
            </button>
          </h2>
          <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne">
            <div class="accordion-body">
              <ul class="accordian-submenu">
                <li><a href="{{route('vendor.rfq.received.index')}}">RFQ Received</a></li>
                <!-- <li><a href="javascript:void(0)">Quotation Sent</a></li>
                <li><a href="javascript:void(0)">Counter Offer</a></li> -->
                <li><a href="{{route('vendor.rfq.live-auction.index')}}">Live Auction RFQ</a></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingFive">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
              data-bs-target="#collapseFive" aria-expanded="false" aria-controls="collapseFive">
              <span class="sidebar-icon"><span class="bi bi-check2-square" aria-hidden="true"></span></span>
              <span class="nav-text">Order Confirmed</span>
            </button>
          </h2>
          <div id="collapseFive" class="accordion-collapse collapse" aria-labelledby="headingFive">
            <div class="accordion-body">
              <ul class="accordian-submenu">
                <li><a href="{{route('vendor.rfq_order.index')}}">RFQs Order</a></li>
                <li><a href="{{route('vendor.direct_order.index')}}">Direct Order</a></li>
              </ul>
            </div>
          </div>
        </div>
        <div>
          <a href="{{ route('vendor.products.index') }}" class="menu-text-colour submenu-list">
            <span class="sidebar-icon"><span class="bi bi-layers" aria-hidden="true"></span></span>
            <span class="nav-text">View/Add Products</span></a>
        </div>
        <!-- <div class="accordion-item">
          <h2 class="accordion-header" id="headingThree">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
              data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
              <span class="sidebar-icon"><span class="bi bi-clipboard2" aria-hidden="true"></span></span>
              <span class="nav-text">Reports</span>
            </button>
          </h2>
          <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree">
            <div class="accordion-body">
              <ul class="accordian-submenu" aria-labelledby="headingThree">
                <li><a href="javascript:void(0)">RFQs Received</a></li>
                <li><a href="javascript:void(0)">RFQ Responded</a></li>
                <li><a href="javascript:void(0)">Order Confirmation Summary</a></li>
                <li><a href="javascript:void(0)">Product Order Details</a></li>
              </ul>
            </div>
          </div>
        </div> -->
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingTwo">
            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
              data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
              <span class="sidebar-icon"><span class="bi bi-gear" aria-hidden="true"></span></span><span
                class="nav-text">Setting</span>
            </button>
          </h2>
          <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo">
            <div class="accordion-body">
              <ul class="accordian-submenu" aria-labelledby="headingTwo">
                <li><a href="{{route('vendor.profile')}}">My Profile</a></li>
                <li><a href="{{route('vendor.password.change')}}">Change Password</a></li>
                <li><a href="{{route('vendor.web-pages.index')}}">Mini Web Page Management</a></li>
              </ul>
            </div>
          </div>
        </div>
        <div class="accordion-item">
          <h2 class="accordion-header" id="headingSix">
            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSix"
              aria-expanded="false" aria-controls="collapseSix">
              <span class="sidebar-icon"><span class="bi bi-chat-dots" aria-hidden="true"></span></span>
              <span class="nav-text">Message</span>
            </button>
          </h2>
          <div id="collapseSix" class="accordion-collapse collapse show" aria-labelledby="headingSix">
            <div class="accordion-body">
              <ul class="accordian-submenu" aria-labelledby="headingSix">
                <li><a href="javascript:void(0)">Buyer (<span>5</span>)</a></li>
                <li><a href="javascript:void(0)">RaProcure (<span>0</span>)</a></li>
              </ul>
            </div>
          </div>
        </div>
        <div>
          <a href="javascript:void(0)" class="menu-text-colour submenu-list">
            <span class="sidebar-icon"><span class="bi bi-info-circle-fill" aria-hidden="true"></span></span>
            <span class="nav-text">Buyer Query</span></a>
        </div>
      </div>
    </div>
  </aside>
</div>