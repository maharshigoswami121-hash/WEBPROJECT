// Products page filtering and sorting functionality
document.addEventListener('DOMContentLoaded', function ()
{
    // Only run on products page
    if (document.getElementById('products-container')) {
        initProductFilters();
    }

    // Update cart badge on all pages
    updateCartBadge();
});

function initProductFilters()
{
    const productsContainer=document.getElementById('products-container');
    if (!productsContainer) return;

    const productItems=Array.from(productsContainer.querySelectorAll('.product-item'));
    const priceFilters=document.querySelectorAll('.price-filter');
    const ratingFilters=document.querySelectorAll('.rating-filter');
    const productCount=document.getElementById('product-count');

    // Store original products for reset
    const originalProducts=productItems.map(item => ({
        element: item,
        price: parseFloat(item.dataset.price)||0,
        rating: parseInt(item.dataset.rating)||0
    }));

    // Function to filter and display products
    function filterAndDisplayProducts()
    {
        // Get active price filters
        const activePriceRanges=[];
        priceFilters.forEach(filter =>
        {
            if (filter.checked) {
                activePriceRanges.push({
                    min: parseFloat(filter.dataset.min)||0,
                    max: parseFloat(filter.dataset.max)||Infinity
                });
            }
        });

        // Get active rating filters
        const activeRatings=[];
        ratingFilters.forEach(filter =>
        {
            if (filter.checked) {
                activeRatings.push(parseInt(filter.dataset.rating)||0);
            }
        });

        // Filter products
        let filteredProducts=originalProducts.filter(product =>
        {
            // Price filter
            const priceMatch=activePriceRanges.length===0||activePriceRanges.some(range =>
            {
                return product.price>=range.min&&product.price<=range.max;
            });

            // Rating filter
            const ratingMatch=activeRatings.length===0||activeRatings.includes(product.rating);

            return priceMatch&&ratingMatch;
        });

        // Hide all products first
        productItems.forEach(item =>
        {
            item.style.display='none';
        });

        // Show filtered and sorted products (and reorder DOM to reflect sort)
        filteredProducts.forEach(product =>
        {
            product.element.style.display='block';
            productsContainer.appendChild(product.element);
        });

        // Update product count
        if (productCount) {
            productCount.textContent=filteredProducts.length;
        }

        // Show message if no products found
        let noResultsMsg=document.getElementById('no-results-message');
        if (filteredProducts.length===0) {
            if (!noResultsMsg) {
                noResultsMsg=document.createElement('div');
                noResultsMsg.id='no-results-message';
                noResultsMsg.className='col-12 text-center py-5';
                noResultsMsg.innerHTML=`
                    <svg width="64" height="64" fill="none" stroke="currentColor" viewBox="0 0 24 24" class="text-muted mb-3">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <h4 class="fw-bold mb-2">No products found</h4>
                    <p class="text-muted">Try adjusting your filters to see more products.</p>
                `;
                productsContainer.appendChild(noResultsMsg);
            }
            noResultsMsg.style.display='block';
        } else {
            if (noResultsMsg) {
                noResultsMsg.style.display='none';
            }
        }
    }

    // Add event listeners to price filters
    priceFilters.forEach(filter =>
    {
        filter.addEventListener('change', filterAndDisplayProducts);
    });

    // Add event listener to rating filters
    ratingFilters.forEach(filter =>
    {
        filter.addEventListener('change', filterAndDisplayProducts);
    });

    // Initial filter (in case some filters are unchecked by default)
    filterAndDisplayProducts();
}

// Check if user is logged in (set by PHP in header)
function isUserLoggedIn()
{
    // This will be set by PHP in pages that need it
    return typeof window.userLoggedIn!=='undefined'? window.userLoggedIn:false;
}

// Update cart badge function
function updateCartBadge()
{
    const cart=JSON.parse(localStorage.getItem('cart'))||[];
    const totalItems=cart.reduce((sum, item) => sum+(item.quantity||0), 0);
    const badge=document.querySelector('.cart-badge');
    if (badge) {
        badge.textContent=totalItems;
    }
}
