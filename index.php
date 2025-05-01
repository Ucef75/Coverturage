<?php
require_once 'server/language.php';

// Make the variables available in current scope
$selectedLang = $GLOBALS['selectedLang'];
$selectedCountry = $GLOBALS['selectedCountry'];
$languages = $GLOBALS['languages'];
$countries = $GLOBALS['countries'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['login'])) {
        header("Location: pages/login.php?lang=$selectedLang&country=$selectedCountry");
        exit();
    } elseif (isset($_POST['signup'])) {
        header("Location: pages/signup.php?lang=$selectedLang&country=$selectedCountry");
        exit();
    }
}
?>

<?php if (isset($_GET['mailsent'])): ?>
    <div class="alert <?= $_GET['mailsent'] ? 'alert-success' : 'alert-error' ?>">
        <?= $_GET['mailsent'] ? 
            t('message_sent', 'Message sent successfully!') : 
            t('send_error', 'Error sending message. Please try again.') ?>
    </div>
<?php endif; ?>
<!DOCTYPE html>
<html lang="<?= htmlspecialchars($selectedLang) ?>" dir="<?= $languages[$selectedLang]['dir'] ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ForsaDrive</title>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link rel="stylesheet" href="css/index.css?v=<?= time() ?>">
    <?php if ($languages[$selectedLang]['dir'] === 'rtl'): ?>
    <link rel="stylesheet" href="css/rtl.css">
    <?php endif; ?>
</head>
<body>
    <!-- Header Section -->
    <header id="mainHeader">
        <nav>
            <div class="logo">Forsa<span>Drive</span></div>
            
            <ul>
                <li>
                    <select id="countrySelect" class="country-selector">
                        <?php foreach ($countries as $code => $name): ?>
                            <option value="<?= htmlspecialchars($code) ?>" <?= $code === $selectedCountry ? 'selected' : '' ?>>
                                <?= htmlspecialchars($name) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </li>
                <li>
                    <select id="languageSelect" class="country-selector">
                        <?php foreach ($languages as $code => $lang): ?>
                            <option value="<?= htmlspecialchars($code) ?>" <?= $code === $selectedLang ? 'selected' : '' ?>>
                                <?= htmlspecialchars($lang['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </li>
                <li><a href="#home"><?= t('home', 'Home') ?></a></li>
                <li><a href="#how-it-works"><?= t('how_it_works', 'How It Works') ?></a></li>
                <li><a href="#reviews"><?= t('reviews_title', 'Reviews') ?></a></li>
                <li><a href="#location"><?= t('contact', 'Contact Us') ?></a></li>
                <li>
                    <form method="post" style="display: inline;">
                        <button type="submit" name="login" class="btn-login"><?= t('login', 'Login') ?></button>
                    </form>
                </li>
                <li>
                    <form method="post" style="display: inline;">
                        <button type="submit" name="signup" class="btn-signup"><?= t('signup', 'Sign Up') ?></button>
                    </form>
                </li>
            </ul>
        </nav>
    </header>

    <!-- Home Section -->
    <section id="home" class="section">
        <div class="container">
            <h1><?= t('welcome', 'Welcome to ForsaDrive') ?></h1>
            <p><?= t('slogan', 'Your reliable ride-sharing solution available across the Middle East and North Africa.') ?></p>
            <p><?= t('description', 'ForsaDrive is a cutting-edge app that connects drivers and passengers, making transportation more efficient, affordable, and eco-friendly.') ?></p>
            
            <div class="stats">
                <div class="stat-item">
                    <div class="stat-number">$200M+</div>
                    <div class="stat-label"><?= t('stats.valuation', 'Company Valuation') ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">400k</div>
                    <div class="stat-label"><?= t('stats.rides', 'Daily Rides') ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">6</div>
                    <div class="stat-label"><?= t('stats.countries', 'Countries') ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">15M</div>
                    <div class="stat-label"><?= t('stats.users', 'Happy Users') ?></div>
                </div>
            </div>
            
            <div class="download-buttons">
                <a href="https://www.apple.com/" target="_blank" class="download-btn"><i class="fab fa-apple"></i> <?= t('download', 'Download Now') ?></a>
                <a href="https://play.google.com/store/games?hl=en" target="_blank" class="download-btn"><i class="fab fa-google-play"></i> <?= t('download', 'Download Now') ?></a>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section id="how-it-works" class="section">
        <div class="container">
            <h2><?= t('how_it_works', 'How It Works') ?></h2>
            <ol class="steps">
                <?php foreach (t('steps', []) as $step): ?>
                    <li><?= $step ?></li>
                <?php endforeach; ?>
            </ol>
        </div>
    </section>

    <!-- Reviews Section -->
    <section id="reviews" class="section">
        <div class="container">
            <h2><?= t('reviews_title', 'What Our Users Say') ?></h2>
            <div class="reviews">
                <?php 
                $reviews = t('reviews', []);
                $reviewers = t('reviewers', []);
                for ($i = 0; $i < min(3, count($reviews), count($reviewers)); $i++): 
                ?>
                    <div class="review">
                        <p>"<?= $reviews[$i] ?>"</p>
                        <p>- <?= $reviewers[$i] ?></p>
                    </div>
                <?php endfor; ?>
            </div>
        </div>
    </section>
    <!-- Location Section -->
    <section id="location" class="section">
        <div class="container">
            <h2><?= t('location_title', 'Our Location') ?></h2>
            <p><?= t('visit_hq', 'Visit our headquarters in Kélibia, Tunisia:') ?></p>
            <div class="map-container">
                <div id="map"></div>
            </div>
            <div class="map-info">
                <p><i class="fas fa-map-marker-alt"></i> <strong><?= t('address', 'Address') ?>:</strong> 8025 Hammam Al Ghezaz, Nabeul, Tunisia</p>
                <p><i class="fas fa-clock"></i> <strong><?= t('working_hours', 'Working Hours') ?>:</strong> Monday-Friday: 9:00 AM - 2:00 PM</p>
            </div>
        </div>
    </section>
      <  <!-- Contact Section -->
        <section id="contact" class="section">
        <div class="container">
            <h2><?= t('contact_us', 'Contact Us') ?></h2>
            <div class="contact-form-container">
                <form action="server/send_contact.php" method="post" class="contact-form">
                    <div class="form-group">
                        <input type="text" name="name" placeholder="<?= t('your_name', 'Your Name') ?>" required>
                    </div>
                    <div class="form-group">
                        <input type="email" name="email" placeholder="<?= t('your_email', 'Your Email') ?>" required>
                    </div>
                    <div class="form-group">
                        <textarea name="message" placeholder="<?= t('leave_message', 'Your message...') ?>" required></textarea>
                    </div>
                    <button type="submit" class="btn-submit"><?= t('send_message', 'Send Message') ?></button>
                </form>
            </div>
        </div>
    </section>
    <?php require 'include/footer.php'; ?>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="js/index.js"></script>
    <script>
        document.getElementById('countrySelect').addEventListener('change', function() {
            window.location.href = `?country=${this.value}&lang=<?= $selectedLang ?>`;
        });

        document.getElementById('languageSelect').addEventListener('change', function() {
            window.location.href = `?lang=${this.value}&country=<?= $selectedCountry ?>`;
        });
    </script>

</body>
</html>