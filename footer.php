<!-- Footer -->
<footer>
    <div class="container">
        <div class="footer-container">
            <div class="footer-main">
                <div class="footer-brand">
                    <h3>SALMA Solutions</h3>
                    <p>PT Satu Langkah Maju Bersama</p>
                    <p>Konsultan Bisnis | Headhunter | Produk Digital HR</p>
                    <div class="social-links">
                        <a href="https://wa.me/6281901763203" target="_blank" title="WhatsApp">
                            <i class="fab fa-whatsapp"></i>
                        </a>
                        <a href="https://instagram.com/satulangkah_bersama" target="_blank" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="mailto:satulangkahmajubersama@gmail.com" title="Email">
                            <i class="fas fa-envelope"></i>
                        </a>
                        <a href="https://goo.gl/maps/gX8YrTqo2JQesePCA" target="_blank" title="Lokasi">
                            <i class="fas fa-map-marker-alt"></i>
                        </a>
                    </div>
                </div>
                
                <div class="footer-contact-map">
                    <div class="contact-info">
                        <h3>Kontak & Lokasi</h3>
                        <ul class="footer-links">
                            <li>
                                <i class="fab fa-whatsapp"></i> 
                                <a href="https://wa.me/6281901763203" target="_blank">+62 819-0176-3203</a>
                            </li>
                            <li>
                                <i class="fas fa-envelope"></i> 
                                <a href="mailto:satulangkahmajubersama@gmail.com">satulangkahmajubersama@gmail.com</a>
                            </li>
                            <li>
                                <i class="fab fa-instagram"></i> 
                                <a href="https://instagram.com/satulangkah_bersama" target="_blank">@satulangkah_bersama</a>
                            </li>
                            <li>
                                <i class="fas fa-map-marker-alt"></i> 
                                <a href="https://goo.gl/maps/gX8YrTqo2JQesePCA" target="_blank">Sampangan, Semarang, Jawa Tengah</a>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="map-container">
                        <iframe 
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3960.34839167358!2d110.414474!3d-7.005543!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x0%3A0x0!2zN8KwMDAnMTkuOSJTIDExMMKwMjQnNTIuMSJF!5e0!3m2!1sid!2sid!4v1620000000000!5m2!1sid!2sid" 
                            allowfullscreen="" 
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="copyright">
            <p>&copy; 2023 SALMA Solutions - PT Satu Langkah Maju Bersama. All Rights Reserved.</p>
        </div>
    </div>
</footer>

<style>
    /* Footer Styles */
    footer {
        background-color: var(--primary);
        color: white;
        padding: 60px 0 30px;
    }
    
    .footer-container {
        margin-bottom: 40px;
    }
    
    .footer-main {
        display: grid;
        grid-template-columns: 1fr 1.5fr;
        gap: 60px;
        align-items: start;
    }
    
    .footer-brand h3 {
        color: white;
        margin-bottom: 15px;
        position: relative;
        padding-bottom: 10px;
        font-size: 1.5rem;
    }
    
    .footer-brand h3:after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 50px;
        height: 2px;
        background-color: var(--accent);
    }
    
    .footer-brand p {
        margin-bottom: 10px;
        color: #ddd;
        line-height: 1.5;
    }
    
    .footer-contact-map {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
        align-items: start;
    }
    
    .contact-info h3 {
        color: white;
        margin-bottom: 20px;
        position: relative;
        padding-bottom: 10px;
        font-size: 1.3rem;
    }
    
    .contact-info h3:after {
        content: '';
        position: absolute;
        left: 0;
        bottom: 0;
        width: 50px;
        height: 2px;
        background-color: var(--accent);
    }
    
    .footer-links {
        list-style: none;
        margin: 0;
        padding: 0;
    }
    
    .footer-links li {
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        transition: transform 0.2s ease;
    }
    
    .footer-links li:hover {
        transform: translateX(5px);
    }
    
    .footer-links li i {
        margin-right: 12px;
        width: 20px;
        color: var(--accent);
        font-size: 1.1rem;
    }
    
    .footer-links a {
        color: #ddd;
        text-decoration: none;
        transition: color 0.3s;
        font-size: 0.95rem;
    }
    
    .footer-links a:hover {
        color: var(--accent);
    }
    
    .social-links {
        display: flex;
        gap: 15px;
        margin-top: 25px;
    }
    
    .social-links a {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 45px;
        height: 45px;
        background-color: rgba(255,255,255,0.1);
        border-radius: 50%;
        color: white;
        transition: all 0.3s;
        text-decoration: none;
    }
    
    .social-links a:hover {
        background-color: var(--accent);
        color: var(--dark);
        transform: translateY(-3px);
        box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }
    
    .map-container {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 5px 20px rgba(0,0,0,0.15);
        height: 250px;
    }
    
    .map-container iframe {
        width: 100%;
        height: 100%;
        border: none;
    }
    
    .copyright {
        text-align: center;
        padding-top: 30px;
        border-top: 1px solid rgba(255,255,255,0.1);
        font-size: 0.9rem;
        color: #ddd;
    }
    
    /* Responsive Footer */
    @media (max-width: 992px) {
        .footer-main {
            grid-template-columns: 1fr;
            gap: 40px;
        }
        
        .footer-contact-map {
            gap: 30px;
        }
    }
    
    @media (max-width: 768px) {
        .footer-contact-map {
            grid-template-columns: 1fr;
            gap: 30px;
        }
        
        .footer-brand,
        .contact-info {
            text-align: center;
        }
        
        .footer-brand h3:after,
        .contact-info h3:after {
            left: 50%;
            transform: translateX(-50%);
        }
        
        .social-links {
            justify-content: center;
        }
        
        .footer-links li {
            justify-content: center;
        }
        
        .map-container {
            height: 200px;
        }
    }
    
    @media (max-width: 480px) {
        footer {
            padding: 40px 0 20px;
        }
        
        .footer-main {
            gap: 30px;
        }
        
        .footer-contact-map {
            gap: 25px;
        }
        
        .social-links {
            gap: 10px;
        }
        
        .social-links a {
            width: 40px;
            height: 40px;
        }
    }
</style>