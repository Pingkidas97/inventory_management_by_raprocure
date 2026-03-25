/***Scroll to top */
if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
}
window.scrollTo(0, 0);

/***Notification Modal*/

function setNotify(event) {
    event.stopPropagation();
    var getNotification = document.getElementById("Allnotification_messages");

    if (getNotification) {
        getNotification.classList.toggle('notishow');
    }
    document.body.classList.add("no-scroll");
}
document.addEventListener("click", function () {
    var getNotification = document.getElementById("Allnotification_messages");
    if (getNotification) {
        getNotification.classList.remove('notishow');
    }
    document.body.classList.remove("no-scroll");
});

/****ToolTip */
var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
    var tooltip = new bootstrap.Tooltip(tooltipTriggerEl);

    // Add click listener to hide tooltip
    tooltipTriggerEl.addEventListener('click', function () {
        tooltip.hide();
    });

    return tooltip;
});

/***header-search-division*/
function setSearch(event) {
    event.stopPropagation();
    var getSearch = document.getElementById("category_by_division");
    getSearch.classList.toggle('searchshow');
}
document.addEventListener("click", function () {
    var getSearch = document.getElementById("category_by_division");
    if (getSearch) {
        getSearch.classList.remove('searchshow');
    }
});

// Show the button after scrolling 100px
window.onscroll = function () {
    const btn = document.getElementById("backToTopBtn");
    btn.style.display = (document.documentElement.scrollTop > 100) ? "block" : "none";
};

// Scroll smoothly to the top
function scrollToTop() {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
}

// Start of Toggle filter section
function openOffcanvasFilter() {
    document.getElementById('filterPanel').classList.add('active');
}

function closeOffcanvasFilter() {
    document.getElementById('filterPanel').classList.remove('active');
}
// End of Toggle filter section

document.querySelectorAll('.multiselect-dropdown').forEach(dropdown => {
    const button = dropdown.querySelector('.custom-multiselect-dropdown-btn');
    const checkboxes = dropdown.querySelectorAll('.location-checkbox');

    function updateText() {
        const selected = Array.from(checkboxes)
            .filter(cb => cb.checked)
            .map(cb => cb.value);

        if (selected.length === 0) {
            button.textContent = 'Select Location';
        } else if (selected.length <= 2) {
            button.textContent = selected.join(', ');
        } else {
            const firstTwo = selected.slice(0, 2).join(', ');
            const moreCount = selected.length - 2;
            button.textContent = `${firstTwo} (+${moreCount} more)`;
        }
        button.title = selected.join(', ');
    }

    checkboxes.forEach(cb => cb.addEventListener('change', updateText));
    updateText(); // run once on page load
});

function toggleWishlist(button) {
    let $icon = $(button).find('span.bi');

    if ($icon.hasClass('bi-heart')) {
        $icon.removeClass('bi-heart').addClass('bi-heart-fill');
    } else {
        $icon.removeClass('bi-heart-fill').addClass('bi-heart');
    }
}

// End of Wishlist Button

// Start of Custom Toggle Accordion
document.querySelectorAll('.custom-toggle-accordion').forEach(accordion => {
    accordion.querySelectorAll('[data-bs-toggle="collapse"]').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault(); // Prevent default behavior
            e.stopPropagation();

            const targetId = this.getAttribute('data-bs-target');
            const targetEl = document.querySelector(targetId);

            if (targetEl.classList.contains('show')) {
                const instance = bootstrap.Collapse.getInstance(targetEl);
                if (instance) {
                    instance.hide();
                }
            } else {
                // Avoid interfering with Bootstrap's built-in logic
                bootstrap.Collapse.getOrCreateInstance(targetEl).show();
            }
        });
    });
});
// End of Custom Toggle Accordion

// Start Tooltip for Active RFQ
document.addEventListener("DOMContentLoaded", () => {
    window.showTooltip = function (el) {
        const tooltip = document.createElement('div');
        tooltip.textContent = el.getAttribute('title');
        tooltip.style.position = 'absolute';
        tooltip.style.background = '#333';
        tooltip.style.color = '#fff';
        tooltip.style.padding = '5px 10px';
        tooltip.style.borderRadius = '0';
        tooltip.style.maxWidth = '200px';
        tooltip.style.top = `${el.getBoundingClientRect().top + window.scrollY - 40}px`;
        tooltip.style.left = `${el.getBoundingClientRect().left}px`;
        tooltip.style.zIndex = 100;
        document.body.appendChild(tooltip);

        setTimeout(() => tooltip.remove(), 5000);
        return false;
    };
});
// End Tooltip for Active 


// Start of Add your Vendor Table

function addVendoer() {
    var addVendorTablebody = document.getElementById('addVendorTablebody');
    var addVendorTableRow = document.createElement('tr');
    var rowNumber = addVendorTablebody.rows.length + 1;
    addVendorTableRow.innerHTML = `
            
                    <td>${rowNumber}</td>
                    <td class="text-center">
                      <input type="text" class="vendor-details-field form-control text-center bg-white mx-auto">
                    </td>
                    <td class="text-center"> <input type="text" class="vendor-details-field form-control text-center bg-white mx-auto"></td>
                    <td><input type="email" class="vendor-details-field form-control text-center bg-white mx-auto"></td>
                    <td><input type="text" class="vendor-details-field form-control text-center bg-white mx-auto"></td>
                    <td class="text-center"> <input type="text" class="vendor-details-field form-control text-center bg-white mx-auto"></td>
                    <td><input type="text" class="vendor-details-field form-control text-center bg-white mx-auto"></td>
                    <td></td>
                    <td><button class="bg-transparent border-0" onclick="this.closest('tr').remove()"><span class="bi bi-trash3 text-danger"></span></button</td>
                
`
    addVendorTablebody.appendChild(addVendorTableRow);


}
// End of Add your Vendor Table

// Start of Prevent aria-hidden Warning while close Modal
document.querySelectorAll('[data-bs-dismiss="modal"]').forEach(closeBtn => {
    closeBtn.addEventListener('click', () => {
        // Remove focus from button to prevent aria-hidden warning
        closeBtn.blur();
    });
});
// End of Prevent aria-hidden Warning while close Modal

// Start of Show Hide CIS Toggle Row
const toggleButton = document.querySelector('.toggle-row-button');

if (toggleButton) {
    toggleButton.addEventListener('click', function () {
        const icon = this.querySelector('.bi');
        const rows = document.querySelectorAll('.toggle-row');

        // Determine current visibility based on first toggle-row
        const isVisible = rows[0].style.display !== 'none';

        rows.forEach(row => {
            row.style.display = isVisible ? 'none' : 'table-row';
        });

        icon.classList.toggle('bi-chevron-down', isVisible);
        icon.classList.toggle('bi-chevron-up', !isVisible);
    });
}

// End of Show Hide CIS Toggle Row

// Start of Table Scroll with Next and Previous button
const scrollContainer = document.getElementById('tableScrollContainer');
const scrollStep = 10;       // How much to scroll per step (px)
const scrollTotal = 141;     // Total scroll distance
const scrollInterval = 20;   // Delay between steps (ms)

function smoothScroll(direction) {
    let scrolled = 0;
    const step = direction === 'left' ? -scrollStep : scrollStep;

    const interval = setInterval(() => {
        scrollContainer.scrollLeft += step;
        scrolled += Math.abs(step);
        if (scrolled >= scrollTotal) clearInterval(interval);
    }, scrollInterval);
}

// Only add event listeners if the elements exist
const scrollLeftButton = document.getElementById('scrollLeft');
const scrollRightButton = document.getElementById('scrollRight');

if (scrollLeftButton) {
    scrollLeftButton.addEventListener('click', () => {
        smoothScroll('left');
    });
}

if (scrollRightButton) {
    scrollRightButton.addEventListener('click', () => {
        smoothScroll('right');
    });
}

// End of Table Scroll with Next and Previous button

// Start of Suggestion Dropdown in Multi Product add
document.querySelectorAll('.cis-details-mobile-wrapper').forEach(wrapper => {
    const toggleBtn = wrapper.querySelector('.cis-mobile-toggle-button');
    const toggleContent = wrapper.querySelector('.cis-details-mobile-wrapper-content');
    const toggleIcon = wrapper.querySelector('.toggle-icon');

    // Hide the content by default
    toggleContent.style.display = 'none';
    toggleIcon.className = 'toggle-icon bi bi-chevron-down';

    toggleBtn.addEventListener('click', () => {
        const isVisible = toggleContent.style.display !== 'none';
        toggleContent.style.display = isVisible ? 'none' : 'block';

        toggleIcon.className = isVisible
            ? 'toggle-icon bi bi-chevron-down'
            : 'toggle-icon bi bi-chevron-up';
    });
});
// End of Suggestion Dropdown in Multi Product add

// Start of Show More and Less CIS Toggle Row
document.querySelectorAll('.toggle-show-more-button').forEach(button => {
    button.addEventListener('click', function () {
        const wrapper = this.closest('.list-of-vendors');
        const icon = this.querySelector('.bi');
        const rows = wrapper.querySelectorAll('.toggle-vendor-list');

        // Determine if currently shown
        const isVisible = rows[0].style.display === 'flex';

        // Toggle visibility
        rows.forEach(row => {
            row.style.display = isVisible ? 'none' : 'flex';
        });

        // Toggle icon direction
        icon.classList.toggle('bi-chevron-up', !isVisible);
        icon.classList.toggle('bi-chevron-down', isVisible);

        // Toggle button text
        this.childNodes[0].textContent = isVisible ? 'Show More ' : 'Show Less ';
    });
});

// End of Show More and Less CIS Toggle Row

// Start of Other Link Dropdown Menu
const toggleOtherLinkBtn = document.getElementById('dropdownToggleOtherLink');
const menu = document.getElementById('dropdownMenuOtherLink');

function isMobile() {
    return window.innerWidth <= 768;
}

if (toggleOtherLinkBtn && menu) {
    toggleOtherLinkBtn.addEventListener('click', function (e) {
        if (isMobile()) {
            e.stopPropagation(); // Prevent outside click closing immediately
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        }
    });

    document.addEventListener('click', function () {
        if (isMobile()) {
            menu.style.display = 'none';
        }
    });

    menu.addEventListener('click', function (e) {
        e.stopPropagation(); // Prevent menu from closing when clicking inside
    });
}

// End of Other Link Dropdown Menu

//start of password hide show

function passwordHideShow(currentpassword, passwordEye) {
    var inputValue = document.getElementById(currentpassword);
    var passwordEye = document.getElementById(passwordEye);
    if (inputValue.type === "password") {
        inputValue.type = "text";
        passwordEye.classList.remove('bi-eye-slash');
        passwordEye.classList.add('bi-eye');

    }
    else {
        inputValue.type = "password";
        passwordEye.classList.remove('bi-eye');
        passwordEye.classList.add('bi-eye-slash');
    }

}
//end of password hide show

// Start of Simple Browse Button
document.querySelectorAll('.simple-file-upload').forEach(wrapper => {
    const fileInput = wrapper.querySelector('.real-file-input');
    const fileDisplayBox = wrapper.querySelector('.file-display-box');

    let tooltip = bootstrap.Tooltip.getInstance(fileDisplayBox);

    // Click box to trigger file input
    fileDisplayBox.addEventListener('click', () => fileInput.click());

    // On file select
    fileInput.addEventListener('change', () => {
        if (fileInput.files.length > 0) {
            const fileName = fileInput.files[0].name;
            fileDisplayBox.textContent = fileName;
            fileDisplayBox.setAttribute('title', fileName);

            // Dispose and recreate tooltip
            if (tooltip) {
                tooltip.dispose();
            }

            // Recreate with updated title
            tooltip = new bootstrap.Tooltip(fileDisplayBox, {
                title: fileName
            });
        }
    });
});



// js by developers

$(document).on("input", ".text-upper-case", function () {
    $(this).val(($(this).val()).toUpperCase());
});

//for select file: start
$(document).on('change', '.button-browse :file', function () {
    let input = $(this),
        numFiles = input.get(0).files ? input.get(0).files.length : 1,
        label = input.val().replace(/\\/g, '/').replace(/.*\//, '');

    input.trigger('fileselect', [numFiles, label, input]);
});

$('.button-browse :file').on('fileselect', function (event, numFiles, label, input) {
    let val = numFiles > 1 ? numFiles + ' files selected' : label;
    input.parent('.button-browse').next(':text').val(val);
});
//for select file: end

// Function to validate file size
function validateFileSize(event) {
    const fileInput = event.target;
    // const errorMsg = fileInput.nextElementSibling;
    const maxSize = 2 * 1024 * 1024; // 2MB in bytes

    if (fileInput.files.length > 0) {
        const fileSize = fileInput.files[0].size;
        if (fileSize > maxSize) {
            alert('File size exceeds 2MB limit.');
            fileInput.value = ''; // Clear the file input

            // Get both span elements by their classes
            const spanElements = document.querySelectorAll('.help-block.rfq-file-name, .remove-rfq-file');

            // Add the class 'd-none' to each span element
            spanElements.forEach(function (element) {
                element.classList.add('d-none');
            });
        }
    }
}

// Attach event listener to all input elements of type file
document.querySelectorAll('input[type="file"]').forEach(function (input) {
    input.addEventListener('change', validateFileSize);
});

(function ($) {
    $.fn.selectOption = function (vals, multiple = false) {
        const values = multiple ? vals.split(",") : [vals];
        return this.each(function () {
            const $select = $(this);
            $select.find('option').each(function () {
                const $option = $(this);
                const isSelected = values.includes($option.val());
                $option.prop('selected', isSelected);
            });
        });
    };
    $.fn.sanitizeNumberField = function () {
        return this.each(function () {
            var inputValue = $(this).val();

            // Allow only digits and the first decimal point
            var hasDecimal = false;
            var sanitized = '';

            for (var i = 0; i < inputValue.length; i++) {
                var char = inputValue[i];
                if (char >= '0' && char <= '9') {
                    sanitized += char;
                } else if (char === '.' && !hasDecimal) {
                    sanitized += char;
                    hasDecimal = true;
                }
            }

            // Prevent the value from being just a dot
            if (sanitized === '.') {
                sanitized = '';
            }

            $(this).val(sanitized);
        });
    };
    $.fn.debounceInput = function (callback, delay) {
        return this.each(function () {
            var $el = $(this);
            var debounceTimer;

            $el.on('input', function () {
                clearTimeout(debounceTimer);
                var self = this;
                var args = arguments;
                debounceTimer = setTimeout(function () {
                    callback.apply(self, args);
                }, delay);
            });
        });
    };
    $.fn.disableKeyboard = function () {
        return this.each(function () {
            $(this).on('keypress', function (event) {
                event.preventDefault();
            });
        });
    };

})(jQuery);