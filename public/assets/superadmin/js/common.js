document.addEventListener("DOMContentLoaded", function () {
  /*** Sticky Header ***/
  const header = document.getElementById("project_header");
  const stickyPoint = header.offsetTop;
  const placeholder = document.createElement("div");

  placeholder.style.display = "none";
  placeholder.style.height = `${header.offsetHeight}px`;
  header.after(placeholder);

  window.addEventListener("scroll", () => {
    const isSticky = window.pageYOffset > stickyPoint;
    header.classList.toggle("sticky", isSticky);
    placeholder.style.display = isSticky ? "block" : "none";
  });

  /*** Scroll Restoration ***/
  if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
  }
  window.scrollTo(0, 0);

  /*** Logout Dropdown Toggle ***/
  const logout = document.getElementById("user_logout");

  window.setLogout = function (event) {
    event.stopPropagation();
    if (logout) logout.style.display = logout.style.display === "block" ? "none" : "block";
  };

  /*** Notification Modal Toggle ***/
  const notifications = document.getElementById("Allnotification_messages");

  window.setNotify = function (event) {
    event.stopPropagation();
    if (notifications) notifications.classList.toggle("notishow");
  };

  /*** Global Click - Hide Logout & Notifications ***/
  document.addEventListener("click", () => {
    if (logout) logout.style.display = "none";
    if (notifications) notifications.classList.remove("notishow");
  });

  /*** Sidebar Toggle ***/
  window.openNav = function () {
    const sidebar = document.getElementById("mySidebar");
    if (sidebar) sidebar.style.transform = "translateX(0)";
  };

  window.closeNav = function () {
    const sidebar = document.getElementById("mySidebar");
    if (sidebar) sidebar.style.transform = "translateX(-115%)";
  };

  /*** Bootstrap Tooltips ***/
  const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
  tooltipTriggerList.forEach((el) => {
    const tooltip = new bootstrap.Tooltip(el);
    el.addEventListener("click", () => tooltip.hide());
  });
});
