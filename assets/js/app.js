/**
 * LIBAAS CO. — Main JavaScript
 * Handles: Size selection, Stock display, Cart AJAX, Image gallery, Validation
 */

const LibaasApp = {
    // ── Initialization ──
    init() {
        this.initNavbarScroll();
        this.initToast();
        this.initProductGallery();
        this.initSizeSelector();
        this.initQuantityControls();
        this.initCartActions();
        this.initCheckoutValidation();
    },

    // ── Navbar scroll effect ──
    initNavbarScroll() {
        const navbar = document.querySelector('.navbar');
        if (!navbar) return;

        window.addEventListener('scroll', () => {
            navbar.classList.toggle('scrolled', window.scrollY > 50);
        });
    },

    // ── Toast notification system ──
    initToast() {
        let container = document.querySelector('.toast-container');
        if (!container) {
            container = document.createElement('div');
            container.className = 'toast-container';
            document.body.appendChild(container);
        }
        this.toastContainer = container;
    },

    showToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast-item ${type}`;
        toast.innerHTML = `
            <i class="bi bi-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
            <span>${message}</span>
        `;
        this.toastContainer.appendChild(toast);

        setTimeout(() => {
            toast.style.animation = 'fadeOut 0.3s ease forwards';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    },

    // ── Product Image Gallery ──
    initProductGallery() {
        const mainImage = document.getElementById('mainProductImage');
        const thumbs = document.querySelectorAll('.product-gallery .thumb');

        if (!mainImage || !thumbs.length) return;

        thumbs.forEach(thumb => {
            thumb.addEventListener('click', function() {
                const imgSrc = this.querySelector('img').src;
                mainImage.src = imgSrc;
                thumbs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            });
        });
    },

    // ── Size Selector with Stock Logic ──
    initSizeSelector() {
        const sizeBtns = document.querySelectorAll('.size-btn:not(.disabled)');
        const stockDisplay = document.getElementById('stockDisplay');
        const addToCartBtn = document.getElementById('addToCartBtn');
        const selectedSizeInput = document.getElementById('selectedSize');
        const maxQtyInput = document.getElementById('maxQty');

        if (!sizeBtns.length) return;

        sizeBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                sizeBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');

                const size = this.dataset.size;
                const stock = parseInt(this.dataset.stock);

                // Update hidden input
                if (selectedSizeInput) selectedSizeInput.value = size;

                // Update stock display
                if (stockDisplay) {
                    if (stock > 5) {
                        stockDisplay.className = 'stock-info in-stock';
                        stockDisplay.innerHTML = `<i class="bi bi-check-circle"></i> In Stock (${stock} available)`;
                    } else if (stock > 0) {
                        stockDisplay.className = 'stock-info low-stock';
                        stockDisplay.innerHTML = `<i class="bi bi-exclamation-triangle"></i> Only ${stock} left!`;
                    } else {
                        stockDisplay.className = 'stock-info out-of-stock';
                        stockDisplay.innerHTML = `<i class="bi bi-x-circle"></i> Out of Stock`;
                    }
                    stockDisplay.style.display = 'block';
                }

                // Update max quantity
                if (maxQtyInput) maxQtyInput.value = stock;

                // Enable/disable add to cart
                if (addToCartBtn) {
                    if (stock > 0) {
                        addToCartBtn.disabled = false;
                        addToCartBtn.classList.remove('disabled');
                    } else {
                        addToCartBtn.disabled = true;
                        addToCartBtn.classList.add('disabled');
                    }
                }

                // Reset quantity to 1
                const qtyInput = document.getElementById('quantity');
                if (qtyInput) qtyInput.value = 1;
            });
        });
    },

    // ── Quantity Controls ──
    initQuantityControls() {
        document.addEventListener('click', (e) => {
            if (e.target.matches('.qty-minus, .qty-plus')) {
                const container = e.target.closest('.qty-control');
                const input = container.querySelector('input');
                const max = parseInt(document.getElementById('maxQty')?.value || input.max || 99);
                let val = parseInt(input.value) || 1;

                if (e.target.classList.contains('qty-minus') && val > 1) val--;
                if (e.target.classList.contains('qty-plus') && val < max) val++;

                input.value = val;
            }
        });
    },

    // ── Cart Actions (AJAX) ──
    initCartActions() {
        // Add to Cart
        const addToCartForm = document.getElementById('addToCartForm');
        if (addToCartForm) {
            addToCartForm.addEventListener('submit', (e) => {
                e.preventDefault();

                const size = document.getElementById('selectedSize')?.value;
                if (!size) {
                    this.showToast('Please select a size', 'error');
                    return;
                }

                const formData = new FormData(addToCartForm);

                fetch(addToCartForm.action, {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.showToast(data.message, 'success');
                        this.updateCartBadge(data.cart_count);
                    } else {
                        this.showToast(data.message, 'error');
                    }
                })
                .catch(() => {
                    this.showToast('Something went wrong', 'error');
                });
            });
        }

        // Remove from Cart
        document.querySelectorAll('.remove-from-cart').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                const key = this.dataset.key;

                fetch('cart_action.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=remove&key=${key}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
            });
        });

        // Update Cart Quantity
        document.querySelectorAll('.cart-qty-input').forEach(input => {
            input.addEventListener('change', function() {
                const key = this.dataset.key;
                const qty = parseInt(this.value);

                if (qty < 1) {
                    this.value = 1;
                    return;
                }

                fetch('cart_action.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: `action=update&key=${key}&quantity=${qty}`
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    } else {
                        LibaasApp.showToast(data.message, 'error');
                        this.value = this.dataset.oldQty || 1;
                    }
                });
            });
        });
    },

    // ── Update Cart Badge ──
    updateCartBadge(count) {
        const badges = document.querySelectorAll('.cart-count');
        badges.forEach(badge => {
            badge.textContent = count;
            if (count > 0) {
                badge.style.display = 'flex';
            } else {
                badge.style.display = 'none';
            }
        });
    },

    // ── Checkout Validation ──
    initCheckoutValidation() {
        const checkoutForm = document.getElementById('checkoutForm');
        if (!checkoutForm) return;

        checkoutForm.addEventListener('submit', function(e) {
            let valid = true;
            const fields = this.querySelectorAll('[required]');

            fields.forEach(field => {
                field.classList.remove('is-invalid');
                if (!field.value.trim()) {
                    field.classList.add('is-invalid');
                    valid = false;
                }
            });

            // Phone validation
            const phone = this.querySelector('[name="phone"]');
            if (phone && phone.value.trim()) {
                const phoneRegex = /^[0-9+\-\s]{7,15}$/;
                if (!phoneRegex.test(phone.value.trim())) {
                    phone.classList.add('is-invalid');
                    valid = false;
                }
            }

            if (!valid) {
                e.preventDefault();
                LibaasApp.showToast('Please fill all required fields correctly', 'error');
            }
        });
    }
};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => LibaasApp.init());
