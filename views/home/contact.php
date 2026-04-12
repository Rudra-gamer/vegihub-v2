<?php include VIEW_PATH . '/layouts/header.php'; ?>
<div class="container page-shell">
    <div class="page-breadcrumb"><a href="<?= base_url() ?>">Home</a><span>•</span><span>Contact Us</span></div>

    <section class="editorial-hero">
        <span class="eyebrow">Support and Contact</span>
        <h1>Reach a real team when something matters.</h1>
        <p>Use the contact form for order support, seller questions, product quality issues, or partnership requests. The goal is fast, useful help rather than a dead-end inbox.</p>
    </section>

    <div class="page-grid">
        <section class="content-panel">
            <h2 class="panel-title">Get in Touch</h2>
            <p style="color:var(--text-secondary);margin-bottom:24px;">Have a question, suggestion, or need help? Send a message and we will route it to the right team.</p>

            <div class="card">
                <div class="card-body" style="padding:0;">
                    <form method="POST" action="<?= base_url('contact') ?>">
                        <?= csrf_field() ?>
                        <div class="form-group">
                            <label>Your Name *</label>
                            <input name="name" class="form-control" required value="<?= e(is_logged_in() ? current_user()['name'] : '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Email Address *</label>
                            <input type="email" name="email" class="form-control" required value="<?= e(is_logged_in() ? current_user()['email'] : '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Subject</label>
                            <input name="subject" class="form-control" placeholder="What's this about?">
                        </div>
                        <div class="form-group">
                            <label>Message *</label>
                            <textarea name="message" class="form-control" rows="5" required placeholder="Tell us what's on your mind..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary btn-lg btn-block">📤 Send Message</button>
                    </form>
                </div>
            </div>
        </section>

        <div class="stack-list">
            <section class="info-panel">
                <h3 style="font-weight:800;margin-bottom:18px;">Contact Information</h3>
                <div class="stack-list">
                    <div class="mini-card"><strong>Email</strong><a href="mailto:rudranahak1000@gmail.com">rudranahak1000@gmail.com</a></div>
                    <div class="mini-card"><strong>Phone</strong><span>7064841325</span></div>
                    <div class="mini-card"><strong>Support Hours</strong><span>Mon-Sat, 8AM to 8PM IST</span></div>
                    <div class="mini-card"><strong>Location</strong><span>Odisha, India</span></div>
                </div>
            </section>

            <section class="info-panel">
                <h3 style="font-weight:800;margin-bottom:18px;">Quick FAQ</h3>
                <div class="stack-list">
                    <div class="faq-item"><strong>How do I track my order?</strong><span>Open My Orders, pick the order, and view its live status timeline.</span></div>
                    <div class="faq-item"><strong>What is your return policy?</strong><span>If the produce is not fresh, contact support within 24 hours for help.</span></div>
                    <div class="faq-item"><strong>How can I become a seller?</strong><span>Register as a seller and submit your products for admin approval.</span></div>
                </div>
            </section>
        </div>
    </div>
</div>
<?php include VIEW_PATH . '/layouts/footer.php'; ?>
