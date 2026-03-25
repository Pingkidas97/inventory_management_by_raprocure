// Toggle sidebar
$(document).ready(function() {
    $("#menu-toggle").click(function(e) {
        e.preventDefault();
        $("#wrapper").toggleClass("toggled");
    });
    
    // Activate current menu item
    $('.list-group-item').filter(function() {
        return this.href == location.href;
    }).addClass('active');
});