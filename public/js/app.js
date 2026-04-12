

document.addEventListener('DOMContentLoaded', function() {
    initHeroCarousel();
    initMobileMenu();
    initSearchAutocomplete();
    initDealTimer();
    initScrollAnimations();
    initToasts();
    initQuantitySelectors();
});

function initHeroCarousel() {
    const slides = document.querySelectorAll('.hero-slide');
    const dots = document.querySelectorAll('.hero-dot');
    if (slides.length === 0) return;

    let current = 0;
    const total = slides.length;

    function showSlide(idx) {
        slides.forEach(s => s.classList.remove('active'));
        dots.forEach(d => d.classList.remove('active'));
        slides[idx].classList.add('active');
        if (dots[idx]) dots[idx].classList.add('active');
    }

    function next() {
        current = (current + 1) % total;
        showSlide(current);
    }

    dots.forEach((dot, i) => {
        dot.addEventListener('click', () => {
            current = i;
            showSlide(current);
        });
    });

    setInterval(next, 5000);
}

function initMobileMenu() {
    const hamburger = document.querySelector('.hamburger');
    const catNav = document.querySelector('.header-categories');
    if (!hamburger || !catNav || hamburger.dataset.menuBound === 'true') return;
    hamburger.dataset.menuBound = 'true';

    const toggleMenu = (forceOpen = null) => {
        const willOpen = forceOpen === null ? !catNav.classList.contains('open') : forceOpen;
        catNav.classList.toggle('open', willOpen);
        hamburger.classList.toggle('active', willOpen);
        hamburger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        document.body.classList.toggle('menu-open', willOpen);
    };

    hamburger.addEventListener('click', () => {
        toggleMenu();
    });

    catNav.querySelectorAll('.cat-nav-item').forEach(link => {
        link.addEventListener('click', () => toggleMenu(false));
    });

    document.addEventListener('click', (event) => {
        if (!catNav.classList.contains('open')) return;
        if (hamburger.contains(event.target) || catNav.contains(event.target)) return;
        toggleMenu(false);
    });
}

function initSearchAutocomplete() {
    const searchInput = document.getElementById('search-input');
    if (!searchInput) return;

    let timeout;
    const dropdown = document.createElement('div');
    dropdown.className = 'search-dropdown';
    dropdown.style.cssText = 'position:absolute;top:100%;left:0;right:0;background:white;border-radius:0 0 8px 8px;box-shadow:0 8px 24px rgba(0,0,0,0.12);z-index:1000;display:none;max-height:400px;overflow-y:auto;';
    const searchBar = searchInput.closest('.search-bar');
    if (!searchBar) return;
    searchBar.style.position = 'relative';
    searchBar.appendChild(dropdown);

    searchInput.addEventListener('input', function() {
        clearTimeout(timeout);
        const q = this.value.trim();
        if (q.length < 2) { dropdown.style.display = 'none'; return; }

        timeout = setTimeout(async () => {
            try {
                const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '';
                const res = await fetch(baseUrl + '/api/products/search?q=' + encodeURIComponent(q));
                const json = await res.json();
                const data = json.results || json;
                
                if (data.length === 0) {
                    dropdown.innerHTML = '<div style="padding:16px;color:#6c757d;text-align:center;">No products found</div>';
                } else {
                    dropdown.innerHTML = data.map(p => `
                        <a href="${p.url || (baseUrl + '/products/' + p.slug)}" style="display:flex;align-items:center;gap:12px;padding:12px 16px;text-decoration:none;color:#1a1a2e;transition:background 0.15s;border-bottom:1px solid #f1f3f5;">
                            <img src="${baseUrl}/public/uploads/products/${p.image || 'placeholder.jpg'}" style="width:44px;height:44px;border-radius:8px;object-fit:cover;background:#f8f9fa;" onerror="this.src='https://images.unsplash.com/photo-1540420773420-3366772f4999?w=50&h=50&fit=crop'">
                            <div style="flex:1;min-width:0;">
                                <div style="font-weight:600;font-size:14px;">${escapeHtml(p.name)}</div>
                                <div style="font-size:13px;color:#2D6A4F;font-weight:700;">₹${p.price}/${p.unit}</div>
                            </div>
                        </a>
                    `).join('');
                }
                dropdown.style.display = 'block';
            } catch(e) { console.error(e); }
        }, 300);
    });

    document.addEventListener('click', (e) => {
        if (!searchBar.contains(e.target)) dropdown.style.display = 'none';
    });
}

function initDealTimer() {
    const timerEl = document.querySelector('.deal-timer');
    if (!timerEl) return;

    function updateTimer() {
        const now = new Date();
        const end = new Date(now);
        end.setHours(23, 59, 59, 999);
        const diff = end - now;

        const h = Math.floor(diff / 3600000);
        const m = Math.floor((diff % 3600000) / 60000);
        const s = Math.floor((diff % 60000) / 1000);

        const hoursEl = timerEl.querySelector('[data-hours]');
        const minsEl = timerEl.querySelector('[data-minutes]');
        const secsEl = timerEl.querySelector('[data-seconds]');
        
        if (hoursEl) hoursEl.textContent = String(h).padStart(2, '0');
        if (minsEl) minsEl.textContent = String(m).padStart(2, '0');
        if (secsEl) secsEl.textContent = String(s).padStart(2, '0');
    }

    updateTimer();
    setInterval(updateTimer, 1000);
}

function initScrollAnimations() {
    const elements = document.querySelectorAll('.animate-on-scroll');
    if (elements.length === 0) return;

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-slideUp');
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    elements.forEach(el => observer.observe(el));
}

function initToasts() {
    const container = document.querySelector('.toast-container');
    if (!container) return;
    
    container.querySelectorAll('.toast').forEach(toast => {
        const closeBtn = toast.querySelector('.toast-close');
        if (closeBtn) {
            closeBtn.addEventListener('click', () => {
                toast.style.animation = 'slideDown 0.3s ease reverse';
                setTimeout(() => toast.remove(), 300);
            });
        }
        setTimeout(() => {
            if (toast.parentElement) {
                toast.style.animation = 'slideDown 0.3s ease reverse';
                setTimeout(() => toast.remove(), 300);
            }
        }, 5000);
    });
}

function initQuantitySelectors() {
    document.querySelectorAll('.qty-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const input = this.parentElement.querySelector('.qty-value');
            let val = parseInt(input.value) || 1;
            const min = parseInt(input.min) || 1;
            const max = parseInt(input.max) || 99;

            if (this.dataset.action === 'decrease') {
                val = Math.max(min, val - 1);
            } else {
                val = Math.min(max, val + 1);
            }
            input.value = val;
            input.dispatchEvent(new Event('change'));
        });
    });
}

async function addToCart(productId, qty = 1) {
    const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '';
    try {
        const res = await fetch(baseUrl + '/cart/add', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `product_id=${productId}&quantity=${qty}&_csrf_token=${getCSRF()}`
        });
        const data = await res.json();
        if (data.success) {
            showToast(data.message || 'Added to cart!', 'success');
            updateCartBadge(data.cart_count);
        } else {
            showToast(data.message || 'Failed to add to cart', 'error');
        }
    } catch(e) {
        showToast('Something went wrong', 'error');
    }
}

async function toggleWishlist(productId, btn) {
    const baseUrl = document.querySelector('meta[name="base-url"]')?.content || '';
    try {
        const res = await fetch(baseUrl + '/wishlist/toggle', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `product_id=${productId}&_csrf_token=${getCSRF()}`
        });
        const data = await res.json();
        if (data.success) {
            if (btn) btn.classList.toggle('active', data.in_wishlist);
            showToast(data.message, 'success');
        } else {
            showToast(data.message || 'Please login first', 'error');
        }
    } catch(e) {
        showToast('Something went wrong', 'error');
    }
}

function updateCartBadge(count) {
    document.querySelectorAll('.cart-badge, .mobile-badge').forEach(el => {
        el.textContent = count;
        el.style.display = count > 0 ? '' : 'none';
    });
}

function getCSRF() {
    return document.querySelector('meta[name="csrf-token"]')?.content || 
           document.querySelector('input[name="_csrf_token"]')?.value || '';
}

function showToast(message, type = 'success') {
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container';
        document.body.appendChild(container);
    }

    const icons = { success: '✅', error: '❌', warning: '⚠️', info: 'ℹ️' };
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.innerHTML = `
        <span>${icons[type] || ''}</span>
        <span>${escapeHtml(message)}</span>
        <button class="toast-close">×</button>
    `;

    container.appendChild(toast);
    toast.querySelector('.toast-close').addEventListener('click', () => {
        toast.style.animation = 'slideDown 0.3s ease reverse';
        setTimeout(() => toast.remove(), 300);
    });

    setTimeout(() => {
        if (toast.parentElement) {
            toast.style.animation = 'slideDown 0.3s ease reverse';
            setTimeout(() => toast.remove(), 300);
        }
    }, 4000);
}

function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function confirmAction(message) {
    return confirm(message);
}

function toggleSidebar() {
    const sidebar = document.querySelector('.sidebar');
    const overlay = document.querySelector('.sidebar-overlay');
    if (sidebar) sidebar.classList.toggle('open');
    if (overlay) overlay.classList.toggle('show');
}
