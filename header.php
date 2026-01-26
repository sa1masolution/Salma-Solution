<?php
// Deteksi halaman aktif
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SALMA Solutions - <?php echo $current_page == 'index.php' ? 'Beranda' : 'Produk Digital'; ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Reset & Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        :root {
            --primary: #1a3a5f;
            --secondary: #4a8c5e;
            --accent: #f8b739;
            --light: #f5f7fa;
            --dark: #2c3e50;
            --gray: #6c757d;
        }
        
        body {
            line-height: 1.6;
            color: var(--dark);
            background-color: var(--light);
        }
        
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 15px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 30px;
            background-color: var(--secondary);
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            text-align: center;
        }
        
        .btn:hover {
            background-color: #3a7a4e;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .btn-accent {
            background-color: var(--accent);
            color: var(--dark);
        }
        
        .btn-accent:hover {
            background-color: #e6a82e;
        }
        
        .btn-outline {
            background-color: transparent;
            border: 2px solid var(--secondary);
            color: var(--secondary);
        }
        
        .btn-outline:hover {
            background-color: var(--secondary);
            color: white;
        }
        
        /* Top Bar dengan Jam Operasional */
        .top-bar {
            background: linear-gradient(90deg, #1a3a5f 0%, #2c5282 50%, #1a3a5f 100%);
            color: white;
            padding: 10px 0;
            font-size: 0.9rem;
            position: relative;
            overflow: hidden;
        }
        
        .top-bar::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.3),
                transparent
            );
            animation: spotlight 10s ease-in-out infinite;
        }
        
        @keyframes spotlight {
            0% {
                left: -100%;
            }
            15% {
                left: 100%;
            }
            100% {
                left: 100%;
            }
        }
        
        .top-bar-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            z-index: 1;
        }
        
        .business-hours {
            display: flex;
            align-items: center;
            position: relative;
        }
        
        .business-hours i {
            margin-right: 8px;
            color: var(--accent);
        }
        
        .contact-top {
            display: flex;
            align-items: center;
        }
        
        .contact-top i {
            margin-right: 8px;
            color: var(--accent);
        }
        
        .contact-top a {
            color: white;
            text-decoration: none;
            margin-left: 20px;
            transition: color 0.3s;
        }
        
        .contact-top a:hover {
            color: var(--accent);
        }
        
        /* Header & Navigation */
        header {
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
        }
        
        .logo {
            display: flex;
            align-items: center;
            position: relative;
        }
        
        .logo-image {
            height: 50px;
            margin-right: 10px;
        }
        
        .logo-text {
            font-size: 1.5rem;
            font-weight: 700;
            color: #066DB5;
            position: relative;
            overflow: hidden;
            padding: 5px 10px;
            border-radius: 5px;
        }
        
      
        
        .logo-text span {
            color: #06A1F9;
            position: relative;
            z-index: 1;
        }
        

        

        
        nav ul {
            display: flex;
            list-style: none;
            align-items: center;
        }
        
        nav ul li {
            margin-left: 25px;
        }
        
        nav ul li a {
            text-decoration: none;
            color: var(--dark);
            font-weight: 600;
            transition: color 0.3s;
            position: relative;
        }
        
        nav ul li a:hover {
            color: var(--secondary);
        }
        
        nav ul li a:after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 0;
            height: 2px;
            background-color: var(--secondary);
            transition: width 0.3s;
        }
        
        nav ul li a:hover:after {
            width: 100%;
        }
        
        .nav-button {
            background-color: var(--accent);
            color: var(--dark);
            padding: 10px 20px;
            border-radius: 5px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .nav-button:hover {
            background-color: #e6a82e;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 1.5rem;
            color: var(--primary);
            cursor: pointer;
        }
        
        /* Responsive Styles */
        @media (max-width: 992px) {
            .top-bar-container {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }
        }
        
        @media (max-width: 768px) {
            nav {
                display: none;
                position: absolute;
                top: 100%;
                left: 0;
                width: 100%;
                background-color: white;
                box-shadow: 0 5px 10px rgba(0,0,0,0.1);
                padding: 100px;
            }
            
            nav.active {
                display: block;
            }
            
            nav ul {
                flex-direction: column;
                align-items: flex-start;
            }
            
            nav ul li {
                margin: 10px 0;
            }
            
            .mobile-menu-btn {
                display: block;
            }
            
            .contact-top {
                flex-direction: column;
                gap: 5px;
            }
            
            .contact-top a {
                margin-left: 0;
            }
            
            .logo-text {
                font-size: 1.3rem;
            }
        }



        /* ============================================
   TOP BAR STYLES (sama seperti di produk.html)
============================================ */
.top-bar {
    background: linear-gradient(90deg, #1a3a5f 0%, #2c5282 50%, #1a3a5f 100%);
    color: white;
    padding: 10px 0;
    font-size: 0.9rem;
    position: relative;
    overflow: hidden;
    display: block; /* Pastikan selalu ditampilkan */
}

.top-bar::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.3),
        transparent
    );
    animation: spotlight 10s ease-in-out infinite;
}

@keyframes spotlight {
    0% {
        left: -100%;
    }
    15% {
        left: 100%;
    }
    100% {
        left: 100%;
    }
}

.top-bar-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    position: relative;
    z-index: 1;
}

.business-hours {
    display: flex;
    align-items: center;
    position: relative;
}

.business-hours i {
    margin-right: 8px;
    color: var(--accent);
}

.contact-top {
    display: flex;
    align-items: center;
}

.contact-top i {
    margin-right: 8px;
    color: var(--accent);
}

.contact-top a {
    color: white;
    text-decoration: none;
    margin-left: 20px;
    transition: color 0.3s;
}

.contact-top a:hover {
    color: var(--accent);
}

/* Untuk tampilan mobile, atur ulang kontak-top */
@media (max-width: 768px) {
    .top-bar-container {
        flex-direction: column;
        gap: 8px;
        text-align: center;
        padding: 8px 15px;
    }
    
    .business-hours, .contact-top {
        justify-content: center;
        width: 100%;
    }
    
    .contact-top {
        flex-direction: column;
        gap: 5px;
    }
    
    .contact-top span, .contact-top a {
        margin-left: 0;
    }
}
    </style>
</head>
<body>
<!-- Top Bar dengan Jam Operasional -->
<div class="top-bar">
    <div class="container top-bar-container">
        <div class="business-hours">
            <i class="far fa-clock"></i>
            <span>Jam Operasional: Senin - Sabtu: 09:00 - 18:00 | Minggu: Tutup</span>
        </div>
        <div class="contact-top">
            <i class="fas fa-phone-alt"></i>
            <span>+62 819-0176-3203</span>
            <a href="mailto:satulangkahmajubersama@gmail.com">
                <i class="far fa-envelope"></i> satulangkahmajubersama@gmail.com
            </a>
        </div>
    </div>
</div>

    <!-- Header & Navigation -->
    <header>
        <div class="container header-container">
            <div class="logo">
                <img src="logo.png" alt="SALMA Solutions" class="logo-image">
                <div class="logo-text">SALMA <span>Solutions</span></div>
            </div>
            <button class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>
            <nav>
                <ul>
                    <?php if ($current_page == 'index.php'): ?>
                        <!-- Menu untuk halaman beranda -->
                        <li><a href="index.php">Beranda</a></li>
                        <li><a href="#about">Tentang Kami</a></li>
                        <li><a href="#services">Layanan</a></li>
                        <li><a href="#team">Tim Kami</a></li>
                        <li><a href="#contact">Kontak</a></li>
                        <li><a href="produk.php" class="nav-button">Produk Digital</a></li>
                    <?php else: ?>
                        <!-- Menu untuk halaman produk -->
                        <li><a href="index.php">Beranda</a></li>
                        <li><a href="produk.php" class="nav-button">Produk Digital</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <script>
        // Mobile menu toggle
        document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
            document.querySelector('nav').classList.toggle('active');
        });
        

    </script>
</body>
</html>