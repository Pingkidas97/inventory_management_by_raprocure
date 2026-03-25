<script>

    function openNav() {
        const sidebar = document.getElementById("mySidebar");
        sidebar.style.transform = "translateX(0)";
        sidebar.classList.add("onClickMenuSidebar"); // Add 'open' class
        window.addEventListener('resize', function () {
            const isMobileView = window.innerWidth <= 768;
            
            if ( !isMobileView) {					
                sidebar.classList.remove("onClickMenuSidebar");
                sidebar.removeAttribute("style");
            }
        });
    }
    
    function closeNav() {
        const sidebar = document.getElementById("mySidebar");
        sidebar.style.transform = "translateX(-115%)";
        sidebar.classList.remove("onClickMenuSidebar"); // Remove 'open' class
        
        let wasMobileView = window.innerWidth <= 768;
        window.addEventListener('resize', function () {
            const isMobileView = window.innerWidth <= 768;                
            if (wasMobileView && !isMobileView) {
                openNav();
                sidebar.classList.remove("onClickMenuSidebar");
                sidebar.removeAttribute("style");
            }
            wasMobileView = isMobileView;
        });
    }
    $(document).on('mouseenter', '#search-by-division', function() {
        // Check if AJAX has already been called to prevent multiple requests
        if (!$(this).data('loaded')) {
            $(this).data('loaded', true); // Mark as loaded
            loadSearchByDivisionList();
        }
    });
    function loadSearchByDivisionList(){
        $.ajax({
            url: '{{ route("buyer.search-by-division") }}',
            method: 'POST',
            dataType: 'json',
            success: function (responce) {                    
                $("#category_by_division").html(responce.divisions);
            },
            error: function () {
                console.error('Search error.');
            }
        });
    }

    @if(session('error'))
        toastr.error("{{ session('error') }}");
    @endif

    @if(session('success'))
        toastr.success("{{ session('success') }}");
    @endif


    // searching
    let currentPage = 1;
    let isLoading = false;
    let hasMoreResults = true;
    let lastSearch = "";
    let is_suggesation = "no";
    let loader_html = ` <li style="text-align: center;" class="search-loader-image">
                            <p><img src="{{ asset('public/assets/images/loader.gif') }}" style="width: 35px;"></p>
                        </li>`;
    var product_search_request;

    $('#product-search').debounceInput(function () {
        const keyword = $(this).val().trim();
        currentPage = 1;
        hasMoreResults = true;
        is_suggesation = "no";
        $('#product-search-list').empty();

        if (keyword.length >= 3) {
            lastSearch = keyword;
            loadMoreResults(keyword, currentPage);
        } else {
            $('#product-search-list').hide();
            if(keyword.length > 0){
                $('#product-search-list').show().html(`<li style="text-align: center;" class="search-loader-image">
                            <p><font style="color:#6aa510;">Please enter more than 3 characters.</font></p>
                        </li>`);
            }
        }
    }, 300);

    // Infinite scroll inside dropdown
    $('#product-search-list').on('scroll', function () {
        const $this = $(this);
        if (
            hasMoreResults &&
            !isLoading &&
            $this.scrollTop() + $this.innerHeight() >= this.scrollHeight - 10
        ) {
            currentPage++;
            loadMoreResults(lastSearch, currentPage);
        }
    });
    function loadMoreResults(keyword, page) {
        isLoading = true;
        $('#product-search-list').show();
        
        if(page == 1){
            $('#product-search-list').html(loader_html);
        }else{
            $('#product-search-list').append(loader_html);
        }
        if(product_search_request && page==1){
            product_search_request.abort();
        }

        product_search_request = $.ajax({
            url: '{{ route("buyer.search.vendor-product") }}',
            method: 'POST',
            data: {
                product_name: keyword,
                page: page,
                source: 'search',
                is_suggesation: is_suggesation
            },
            dataType: 'json',
            success: function (responce) { 
                $(".search-loader-image").remove();                   
                let products = responce.product_html;
                let is_products = responce.is_products;
                is_suggesation = responce.is_suggesation;
                if (is_products) {
                    $('#product-search-list').append(products);
                    hasMoreResults = true;
                } else {
                    if(responce.is_suggesation == "no" && page === 1){
                        is_suggesation = "yes";
                        loadMoreResults(keyword, 1);
                    }
                    if (responce.is_suggesation == "yes" && page === 1) {
                        $('#product-search-list').append('<li><p>No Product found for <b>"'+keyword+'"</b></p></li>');
                    }
                    hasMoreResults = false;
                }
                isLoading = false;
            },
            error: function () {
                console.error('Search error.');
                isLoading = false;
                hasMoreResults = false;
            }
        });
    }
</script>