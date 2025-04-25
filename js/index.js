 // Header scroll effect
 window.addEventListener('scroll', function() {
    const header = document.getElementById('mainHeader');
    if (window.scrollY > 50) {
        header.classList.add('scrolled');
    } else {
        header.classList.remove('scrolled');
    }
});

// Smooth scrolling for anchor links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        
        const targetId = this.getAttribute('href');
        const targetElement = document.querySelector(targetId);
        
        if (targetElement) {
            window.scrollTo({
                top: targetElement.offsetTop - 80,
                behavior: 'smooth'
            });
        }
    });
});

// Country selector functionality
const countrySelect = document.getElementById('countrySelect');
countrySelect.addEventListener('change', function() {
    alert(`You've selected ${this.value}. Service coming soon to this country!`);
});

// Animation on scroll
function animateOnScroll() {
    const elements = document.querySelectorAll('.review, .stat-item, .steps li');
    
    elements.forEach(element => {
        const elementPosition = element.getBoundingClientRect().top;
        const screenPosition = window.innerHeight / 1.2;
        
        if (elementPosition < screenPosition) {
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }
    });
}
// Vérifie que le DOM est bien chargé
// Vérifie que le DOM est bien chargé
// Vérifie que le DOM est bien chargé
document.addEventListener("DOMContentLoaded", function () {
    // Coordonnées de Hammam Al Agzaz (Tunisie)
    const hammamAlAgzazCoords = [36.87752060925882, 11.10656901617463]; // latitude, longitude

    // Initialiser la carte
    const map = L.map('map').setView(hammamAlAgzazCoords, 15);

    // Ajouter une couche de tuiles (OpenStreetMap)
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; OpenStreetMap contributors'
    }).addTo(map);

    // Ajouter un marqueur
    const marker = L.marker(hammamAlAgzazCoords).addTo(map);
    
    // L'adresse exacte pour la popup
    const address = "Hammam Al Alghzez,Nabeul,Tunisia";

    // Ajouter la popup au marqueur
    marker.bindPopup(`<b>ForsaDrive HQ</b><br>${address}`).openPopup();
});

    // Initialize map when page loads
    document.addEventListener('DOMContentLoaded', initMap);

// Set initial state for animated elements
document.querySelectorAll('.review, .stat-item, .steps li').forEach(element => {
    element.style.opacity = '0';
    element.style.transform = 'translateY(20px)';
    element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
});

window.addEventListener('scroll', animateOnScroll);
window.addEventListener('load', animateOnScroll);