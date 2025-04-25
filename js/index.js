// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Header scroll effect
    const header = document.getElementById('mainHeader');
    if (header) {
        window.addEventListener('scroll', function() {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    }

    // Country and language selectors
    const countrySelect = document.getElementById('countrySelect');
    const langSelect = document.getElementById('langSelect');

    if (countrySelect && langSelect) {
        countrySelect.addEventListener('change', function() {
            updateUrlParams({ 
                country: this.value, 
                lang: langSelect.value 
            });
        });

        langSelect.addEventListener('change', function() {
            updateUrlParams({ 
                lang: this.value, 
                country: countrySelect.value 
            });
        });
    }

    // Smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            scrollToTarget(targetId);
        });
    });

    // Initialize map if element exists
    const mapElement = document.getElementById('map');
    if (mapElement) {
        initMap();
    }

    // Animation setup
    setupAnimations();
});

// Helper function to update URL parameters
function updateUrlParams(params) {
    const url = new URL(window.location);
    Object.keys(params).forEach(key => {
        url.searchParams.set(key, params[key]);
    });
    window.location.href = url.toString();
}

// Smooth scroll to target element
function scrollToTarget(targetId) {
    const targetElement = document.querySelector(targetId);
    if (targetElement) {
        const headerHeight = document.getElementById('mainHeader')?.offsetHeight || 80;
        window.scrollTo({
            top: targetElement.offsetTop - headerHeight,
            behavior: 'smooth'
        });
    }
}

// Initialize map function
function initMap() {
    try {
        // Coordinates for Hammam Al Agzaz (Tunisia)
        const hammamAlAgzazCoords = [36.87752060925882, 11.10656901617463];
        
        // Initialize map
        const map = L.map('map').setView(hammamAlAgzazCoords, 15);

        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Add marker with popup
        const marker = L.marker(hammamAlAgzazCoords).addTo(map);
        marker.bindPopup(`
            <b>ForsaDrive HQ</b><br>
            Hammam Al Ghezaz, Nabeul, Tunisia<br>
            <small>Working Hours: Mon-Fri 9:00 AM - 2:00 PM</small>
        `).openPopup();
    } catch (error) {
        console.error('Error initializing map:', error);
    }
}

// Animation functions
function setupAnimations() {
    const animatedElements = document.querySelectorAll('.review, .stat-item, .steps li');
    
    animatedElements.forEach(element => {
        element.style.opacity = '0';
        element.style.transform = 'translateY(20px)';
        element.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
    });

    // Initial check in case elements are already visible
    animateElements();
    
    // Check on scroll and resize
    window.addEventListener('scroll', animateElements);
    window.addEventListener('resize', animateElements);
}

function animateElements() {
    const elements = document.querySelectorAll('.review, .stat-item, .steps li');
    const screenPosition = window.innerHeight / 1.2;
    
    elements.forEach(element => {
        const elementPosition = element.getBoundingClientRect().top;
        
        if (elementPosition < screenPosition) {
            element.style.opacity = '1';
            element.style.transform = 'translateY(0)';
        }
    });
}