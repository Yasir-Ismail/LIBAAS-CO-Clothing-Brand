<!-- Footer -->
<footer class="site-footer">
    <div class="container">
        <div class="row g-4">
            <div class="col-lg-4 col-md-6">
                <div class="footer-brand">LIBAAS <span>CO.</span></div>
                <p style="font-size:0.9rem;color:rgba(255,255,255,0.5);line-height:1.8;">
                    Premium clothing for the modern individual. Crafted with care, designed with purpose.
                </p>
            </div>
            <div class="col-lg-2 col-md-6">
                <h5>Shop</h5>
                <ul>
                    <li><a href="<?= base_url('public/products.php?category=Men') ?>">Men</a></li>
                    <li><a href="<?= base_url('public/products.php?category=Women') ?>">Women</a></li>
                    <li><a href="<?= base_url('public/products.php?category=Kids') ?>">Kids</a></li>
                    <li><a href="<?= base_url('public/products.php') ?>">All Products</a></li>
                </ul>
            </div>
            <div class="col-lg-2 col-md-6">
                <h5>Help</h5>
                <ul>
                    <li><a href="#">Size Guide</a></li>
                    <li><a href="#">Shipping</a></li>
                    <li><a href="#">Returns</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </div>
            <div class="col-lg-4 col-md-6">
                <h5>Stay Connected</h5>
                <p style="font-size:0.85rem;color:rgba(255,255,255,0.5);margin-bottom:1rem;">
                    Follow us for new arrivals and exclusive offers.
                </p>
                <div style="display:flex;gap:1rem;">
                    <a href="#" style="color:rgba(255,255,255,0.6);font-size:1.2rem;"><i class="bi bi-instagram"></i></a>
                    <a href="#" style="color:rgba(255,255,255,0.6);font-size:1.2rem;"><i class="bi bi-facebook"></i></a>
                    <a href="#" style="color:rgba(255,255,255,0.6);font-size:1.2rem;"><i class="bi bi-twitter-x"></i></a>
                    <a href="#" style="color:rgba(255,255,255,0.6);font-size:1.2rem;"><i class="bi bi-tiktok"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            &copy; <?= date('Y') ?> LIBAAS CO. All rights reserved.
        </div>
    </div>
</footer>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Custom JS -->
<script src="<?= base_url('assets/js/app.js') ?>"></script>

</body>
</html>
