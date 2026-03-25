
const header = document.getElementById("project_header");
const stickyPoint = header.offsetTop;

window.addEventListener("scroll", function () {
    if (window.pageYOffset > stickyPoint) {
        header.classList.add("sticky");
    } else {
        header.classList.remove("sticky");
    }
});
$(document).on("input", ".text-upper-case", function () {
    $(this).val(($(this).val()).toUpperCase());
});