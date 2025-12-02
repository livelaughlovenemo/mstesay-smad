<?php
// includes/footer.php
?>
</div> <!-- /container -->

<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-section">
            <h3>Ms. Tesay Chicken</h3>
            <p>Providing quality chicken products since 2010. Your trusted partner for fresh and frozen chicken products.</p>
            <div class="social-links">
                <a href="#" aria-label="Facebook"><i class="fab fa-facebook"></i></a>
                <a href="#" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
                <a href="#" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                <a href="#" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
        
        <div class="footer-section">
            <h4>Contact Info</h4>
            <ul class="contact-info">
                <li><i class="fas fa-map-marker-alt"></i> 123 Chicken Street, Poultry City, 1234</li>
                <li><i class="fas fa-phone"></i> (02) 1234-5678</li>
                <li><i class="fas fa-mobile-alt"></i> +63 912 345 6789</li>
                <li><i class="fas fa-envelope"></i> info@mstesaychicken.com</li>
                <li><i class="fas fa-clock"></i> Open: Mon-Sat, 8:00 AM - 8:00 PM</li>
            </ul>
        </div>
        
        <div class="footer-section">
            <h4>Quick Links</h4>
            <ul class="quick-links">
                <li><a href="../index.php">Home</a></li>
                <li><a href="about.html">About Us</a></li>
                <li><a href="products.html">Products</a></li>
                <li><a href="contact.html">Contact</a></li>
                <li><a href="dashboard.php">Dashboard</a></li>
            </ul>
        </div>
        
        <div class="footer-section">
            <h4>Our Products</h4>
            <ul class="product-links">
                <li><a href="#">Fresh Chicken</a></li>
                <li><a href="#">Frozen Products</a></li>
                <li><a href="#">Processed Meat</a></li>
                <li><a href="#">Specialty Items</a></li>
            </ul>
        </div>
    </div>
    
    <div class="footer-bottom">
        <div class="footer-bottom-container">
            <p>&copy; <?php echo date('Y'); ?> Ms. Tesay Chicken. All rights reserved.</p>
            <div class="footer-legal">
                <a href="privacy.html">Privacy Policy</a> | 
                <a href="terms.html">Terms of Service</a> | 
                <a href="sitemap.html">Sitemap</a>
            </div>
        </div>
    </div>
</footer>

<!-- Font Awesome for icons -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<style>
.site-footer {
    background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
    color: #fff;
    padding: 50px 0 0;
    margin-top: 60px;
    font-family: 'Montserrat', sans-serif;
}

.footer-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 40px;
}

.footer-section h3 {
    color: #F5A200;
    font-size: 24px;
    margin-bottom: 20px;
    font-family: 'Pacifico', cursive;
}

.footer-section h4 {
    color: #F5A200;
    font-size: 18px;
    margin-bottom: 20px;
    position: relative;
    padding-bottom: 10px;
}

.footer-section h4::after {
    content: '';
    position: absolute;
    left: 0;
    bottom: 0;
    width: 50px;
    height: 2px;
    background: #F5A200;
}

.footer-section p {
    line-height: 1.6;
    color: #ccc;
    margin-bottom: 20px;
}

.social-links {
    display: flex;
    gap: 15px;
    margin-top: 20px;
}

.social-links a {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 50%;
    color: #fff;
    text-decoration: none;
    transition: all 0.3s ease;
}

.social-links a:hover {
    background: #F5A200;
    transform: translateY(-3px);
}

.contact-info {
    list-style: none;
    padding: 0;
}

.contact-info li {
    display: flex;
    align-items: flex-start;
    margin-bottom: 15px;
    color: #ccc;
    line-height: 1.5;
}

.contact-info i {
    color: #F5A200;
    margin-right: 10px;
    margin-top: 5px;
    min-width: 20px;
}

.quick-links,
.product-links {
    list-style: none;
    padding: 0;
}

.quick-links li,
.product-links li {
    margin-bottom: 12px;
}

.quick-links a,
.product-links a {
    color: #ccc;
    text-decoration: none;
    transition: all 0.3s ease;
    display: inline-block;
}

.quick-links a:hover,
.product-links a:hover {
    color: #F5A200;
    transform: translateX(5px);
}

.footer-bottom {
    background: rgba(0, 0, 0, 0.3);
    padding: 20px 0;
    margin-top: 50px;
}

.footer-bottom-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 20px;
}

.footer-bottom p {
    color: #ccc;
    margin: 0;
}

.footer-legal {
    display: flex;
    gap: 15px;
}

.footer-legal a {
    color: #ccc;
    text-decoration: none;
    transition: color 0.3s ease;
}

.footer-legal a:hover {
    color: #F5A200;
}

/* Responsive Design */
@media (max-width: 768px) {
    .footer-container {
        grid-template-columns: 1fr;
        gap: 30px;
    }
    
    .footer-bottom-container {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }
    
    .footer-legal {
        justify-content: center;
        flex-wrap: wrap;
    }
    
    .site-footer {
        padding: 40px 0 0;
    }
}

@media (max-width: 480px) {
    .footer-section h3 {
        font-size: 20px;
    }
    
    .footer-section h4 {
        font-size: 16px;
    }
    
    .social-links {
        justify-content: center;
    }
    
    .footer-section {
        text-align: center;
    }
    
    .footer-section h4::after {
        left: 50%;
        transform: translateX(-50%);
    }
    
    .contact-info li {
        justify-content: center;
        text-align: left;
        max-width: 250px;
        margin-left: auto;
        margin-right: auto;
    }
}
</style>

</body>
</html>