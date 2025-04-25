<?php
$countries = ['Tunisia', 'Algeria', 'Morocco', 'Libya', 'Egypt', 'Mauritania'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    
    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <title>ForsaDrive</title>
    <link rel="stylesheet" href="css/index.css">
    <!-- Add these in the head section -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body>
<body>

    <!-- Header Section -->
    <header id="mainHeader">
        <nav>
            <div class="logo">Forsa<span>Drive</span></div>
            <ul>
                <li><a href="#home">Home</a></li>
                <li><a href="#how-it-works">How It Works</a></li>
                <li><a href="#reviews">Reviews</a></li>
                <li>
                    <select id="countrySelect" class="country-selector">
                        <?php foreach ($countries as $country): ?>
                            <option value="<?= $country ?>"><?= $country ?></option>
                        <?php endforeach; ?>
                    </select>
                </li>
<li><a href="#" class="btn-login" id="loginBtn">Login</a></li>
<li><a href="#" class="btn-signup" id="signupBtn">Sign Up</a></li>
            </ul>
        </nav>
    </header>

    <!-- Home Section -->
    <section id="home" class="section">
        <div class="container">
            <h1>Welcome to ForsaDrive</h1>
            <p>Your reliable ride-sharing solution available across the Middle East and North Africa.</p>
            <p>ForsaDrive is a cutting-edge app that connects drivers and passengers, making transportation more efficient, affordable, and eco-friendly.</p>
            
            <div class="stats">
                <div class="stat-item">
                    <div class="stat-number">$200M+</div>
                    <div class="stat-label">Company Valuation</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">$5M+</div>
                    <div class="stat-label">Annual Revenue</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">6</div>
                    <div class="stat-label">Countries</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">100K+</div>
                    <div class="stat-label">Happy Users</div>
                </div>
            </div>
            
            <div class="download-buttons">
                <a href="#" class="download-btn"><i class="fab fa-apple"></i> App Store</a>
                <a href="#" class="download-btn"><i class="fab fa-google-play"></i> Google Play</a>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="section">
        <div class="container">
            <h2>How It Works</h2>
            <ol class="steps">
                <li>Download the ForsaDrive app from the App Store or Google Play.</li>
                <li>Sign up or log in to your account.</li>
                <li>Enter your desired pickup and drop-off locations.</li>
                <li>Choose from available drivers and rides at the price set by the driver.</li>
                <li>Book your ride and make payment (50% upfront).</li>
                <li>Enjoy your ride!</li>
            </ol>
        </div>
    </section>

    <!-- Reviews Section -->
    <section id="reviews" class="section">
        <div class="container">
            <h2>What Our Users Say</h2>
            <div class="reviews">
                <div class="review">
                    <p>"ForsaDrive is a game-changer! Affordable, safe, and easy to use. Highly recommend!"</p>
                    <p>- Ahmed, Tunisia</p>
                </div>
                <div class="review">
                    <p>"The best way to get around Morocco. Convenient, fast, and great drivers!"</p>
                    <p>- Fatima, Morocco</p>
                </div>
                <div class="review">
                    <p>"As an international traveler, I love how ForsaDrive operates in so many countries!"</p>
                    <p>- John, USA</p>
                </div>
            </div>
        </div>
    </section>
    <section id="location" class="section">
    <div class="container">
        <h2>Our Location</h2>
        <p>Visit our headquarters in Kélibia, Tunisia:</p>
        <div class="map-container">
            <div id="map"></div>
        </div>
        <div class="map-info">
            <p><i class="fas fa-map-marker-alt"></i> <strong>Address:</strong>8025 Hammam Al Ghezaz, Nabeul, Tunisia</p>
            <p><i class="fas fa-clock"></i> <strong>Working Hours:</strong> Monday-Friday: 9:00 AM - 2:00 PM</p>
        </div>
    </div>
</section>

    <!-- Footer Section -->
    <footer>
        <div class="footer-content">
            <p>&copy; 2025 ForsaDrive | All Rights Reserved</p>
            <div class="credits">
                <p>Created by Aziz BEN SLIMEN & Youssef BEN ABID</p>
                <div class="contact-info">
                    <a href="tel:+21626295416"><i class="fas fa-phone"></i> Aziz: 26295416 (+216)</a>
                    <a href="tel:+21629131170"><i class="fas fa-phone"></i> Youssef: 29131170 (+216)</a>
                </div>
            </div>
        </div>
    </footer>
    <script src="js/index.js"></script>
</body>
</html>
