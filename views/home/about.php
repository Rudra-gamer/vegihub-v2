<?php include VIEW_PATH . '/layouts/header.php'; ?>
<div class="container page-shell">
    <div class="page-breadcrumb"><a href="<?= base_url() ?>">Home</a><span>•</span><span>About Us</span></div>

    <section class="editorial-hero">
        <span class="eyebrow">About VegiHub</span>
        <h1>Fresh produce, local trust, and cleaner digital buying.</h1>
        <p>We are building a produce marketplace that feels dependable from browsing to doorstep delivery, while creating a stronger bridge between customers and local growers.</p>
    </section>

    <div class="page-grid" style="margin-bottom:28px;">
        <section class="content-panel">
            <h2 class="panel-title">Our Mission</h2>
            <p style="color:var(--text-secondary);line-height:1.8;font-size:16px;">
                At Vegihub, we believe everyone deserves access to fresh, high-quality vegetables. We connect local farmers directly with consumers, cutting out middlemen to ensure you get the freshest produce at fair prices.
            </p>
            <p style="color:var(--text-secondary);line-height:1.8;font-size:16px;margin-top:16px;">
                Our platform empowers small-scale farmers by giving them a digital marketplace to reach more customers. Every purchase you make supports local agriculture and sustainable farming practices.
            </p>
            <div class="stack-list" style="margin-top:18px;">
                <div class="stack-item"><strong>Direct from source</strong>Less distance between farm and kitchen means better freshness and fewer hidden margins.</div>
                <div class="stack-item"><strong>Reliable operations</strong>We focus on stronger order logic, payment verification, and better transparency for shoppers.</div>
                <div class="stack-item"><strong>Long-term trust</strong>Farmers, families, and sellers all need a system that feels stable enough for daily use.</div>
            </div>
        </section>
        <section class="info-panel" style="display:flex;align-items:center;justify-content:center;">
            <img src="https://images.unsplash.com/photo-1488459716781-31db52582fe9?w=500&h=400&fit=crop" alt="Fresh vegetables" style="width:100%;max-width:450px;border-radius:var(--radius-lg);object-fit:cover;">
        </section>
    </div>

    <div style="margin-bottom:28px;">
        <div class="section-header">
            <h2 class="section-title">Why Choose Vegihub</h2>
        </div>
        <div class="support-grid">
            <div class="trust-card">
                <span class="trust-icon">🌾</span>
                <h3>Farm to Table</h3>
                <p>Direct sourcing from verified local farmers within 100km radius. No middlemen, no markup.</p>
            </div>
            <div class="trust-card">
                <span class="trust-icon">🔬</span>
                <h3>Quality Checked</h3>
                <p>Every product undergoes quality checks. We guarantee freshness or provide a full refund.</p>
            </div>
            <div class="trust-card">
                <span class="trust-icon">🌍</span>
                <h3>Eco-Friendly</h3>
                <p>We use sustainable packaging and optimize delivery routes to minimize our carbon footprint.</p>
            </div>
            <div class="trust-card">
                <span class="trust-icon">❤️</span>
                <h3>Community First</h3>
                <p>Supporting local farmers, creating rural jobs, and building a healthier community.</p>
            </div>
        </div>
    </div>

    <section class="content-panel" style="margin-bottom:28px;">
        <h2 class="panel-title">Our Impact</h2>
        <div class="metric-row">
            <div class="metric-card"><strong>500+</strong><span>Local farmers on the network</span></div>
            <div class="metric-card"><strong>10K+</strong><span>Customers using the marketplace</span></div>
            <div class="metric-card"><strong>50K+</strong><span>Orders delivered across produce categories</span></div>
            <div class="metric-card"><strong>98%</strong><span>Reported satisfaction on freshness and service</span></div>
        </div>
    </section>

    <section class="content-panel" style="text-align:center;">
        <h2 class="panel-title">Ready to Eat Fresh?</h2>
        <p style="color:var(--text-secondary);margin-bottom:24px;">Join thousands of families who trust Vegihub for their daily vegetables.</p>
        <div class="split-actions" style="justify-content:center;">
            <a href="<?= base_url('products') ?>" class="btn btn-primary btn-lg">🛍️ Start Shopping</a>
            <a href="<?= base_url('register') ?>" class="btn btn-accent btn-lg">🏪 Become a Seller</a>
        </div>
    </section>
</div>
<?php include VIEW_PATH . '/layouts/footer.php'; ?>
