/***Scroll to top */
if ('scrollRestoration' in history) {
  history.scrollRestoration = 'manual';
}
window.scrollTo(0, 0);

document.addEventListener('input', function (e) {

    const allowedTypes = ['text', 'password', 'search', 'email', 'url', 'tel'];

    if (e.target.tagName === 'TEXTAREA' || allowedTypes.includes(e.target.type)) {

        // Allowed characters (â‚¹, $, à¤°à¥ = "à¤°" + "à¥")
        // const allowed = /[^A-Za-z0-9~!@#$%^&*()_+\`\-={}[\]:;"'|\\<,>.?\/ \n\râ‚¹$à¤°à¥]/g;
        const allowed = /[^A-Za-z0-9~!@#$%^&*()_+\`\-={}[\]:;"'|\\<,>.?\/ \n\râ‚¹$à¤°à¥\u200B]/g;

        e.target.value = e.target.value.replace(allowed, '');
    }
});

/***Notification Modal*/

function setNotify(event) {
  event.stopPropagation();
  var getNotification = document.getElementById("Allnotification_messages");

  if (getNotification) {
    getNotification.classList.toggle('notishow');
    document.body.classList.add("no-scroll");
  }
}

document.addEventListener("click", function () {
  var getNotification = document.getElementById("Allnotification_messages");

  if (getNotification) {
    getNotification.classList.remove('notishow');
    document.body.classList.remove("no-scroll");
  }
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

  if (getSearch) {
    getSearch.classList.toggle('searchshow');
  }
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
  window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Start of Custom file input button
document.querySelectorAll('.file-upload-block').forEach(block => {
  const fileInput = block.querySelector('.file-upload');
  const fileInfo = block.querySelector('.file-info');
  const fileUploadWrapper = block.querySelector('.file-upload-wrapper');
  const customFileTrigger = block.querySelector('.custom-file-trigger');

  customFileTrigger.addEventListener('click', () => fileInput.click());

  fileInput.addEventListener('change', () => {
    if (fileInput.files.length > 0) {
      const fileName = fileInput.files[0].name;
      fileInfo.innerHTML = `
      <div class="d-flex align-item-center gap-1 remove-file">
        <span class="display-file font-size-12">${fileName}</span>
        <i class="bi bi-trash3 text-danger font-size-12 ml-3 " style="cursor:pointer;"></i>
      </div>
      `;
      fileInfo.style.display = 'block';
      fileUploadWrapper.style.display = 'none';

      fileInfo.querySelector('.remove-file').addEventListener('click', () => {
        fileInput.value = '';
        fileInfo.innerHTML = '';
        fileInfo.style.display = 'none';
        fileUploadWrapper.style.display = 'block';
      });
    }
  });
});
// End of Custom file input button

// Start of Toggle filter section
function openOffcanvasFilter() {
  document.getElementById('filterPanel').classList.add('active');
}

function closeOffcanvasFilter() {
  document.getElementById('filterPanel').classList.remove('active');
}
// End of Toggle filter section

// Start of Card vendor list scroll
function matchAllScrollHeights() {
  const scrollSections = document.querySelectorAll('.card-vendor-list-search-panel');
  const mainContents = document.querySelectorAll('.card-vendor-list-right-panel');

  if (window.innerWidth < 768) {
    // Remove inline height on mobile view
    scrollSections.forEach(section => {
      section.style.removeProperty('height');
    });
  } else {
    // Match heights on larger screens
    for (let i = 0; i < scrollSections.length; i++) {
      if (mainContents[i]) {
        const extraHeight = 20;
        const removeExtraHeight = 98;
        scrollSections[i].style.height = (mainContents[i].offsetHeight + extraHeight - removeExtraHeight) + 'px';
      }
    }
  }
}

window.addEventListener('load', matchAllScrollHeights);
window.addEventListener('resize', matchAllScrollHeights);

// End of Card vendor list scroll

// Start of Custom Multiselect Dropdown Menu

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
// End of Custom Multiselect Dropdown Menu

// Start of Wishlist Button
function toggleWishlist(button) {
  const icon = button.querySelector('span.bi');

  // Toggle classes between heart and heart-fill
  if (icon.classList.contains('bi-heart')) {
    icon.classList.remove('bi-heart');
    icon.classList.add('bi-heart-fill');
  } else {
    icon.classList.remove('bi-heart-fill');
    icon.classList.add('bi-heart');
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

// End of Simple Browse Button

// Start of Message details fade in/out
  class MessageToggle {
    constructor(container) {
      this.container = container;
      this.sender = container.querySelector('.message-sender');
      this.subject = container.querySelector('.message-subject');
      this.detail = container.querySelector('.message-detail-container');
      this.backButton = container.querySelector('.back-btn');

      this.init();
    }

    init() {
      this.detail.classList.remove('show');
      this.sender.addEventListener('click', () => this.showDetail());
      this.subject.addEventListener('click', () => this.showDetail());
      this.backButton.addEventListener('click', () => this.hideDetail());
    }

    showDetail() {
      this.detail.classList.add('show');
    }

    hideDetail() {
      this.detail.classList.remove('show');
      // Wait for fade-out to complete before showing sender again
      setTimeout(() => {
        this.sender.style.display = '';
        this.subject.style.display = '';
      }, 300); // Match transition duration
    }
  }

  document.querySelectorAll('.chat-list-container').forEach(block => {
    new MessageToggle(block);
  });
// End of Message details fade in/out 