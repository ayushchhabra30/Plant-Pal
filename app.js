/*
 * app.js
 * This file is UPDATED.
 * It includes all your original code, plus new logic for:
 * 1. Opening and closing the Sign Up / Sign In modals.
 * 2. Handling the form submissions with the fetch() API to call your new PHP scripts.
 * 3. Dynamically fetching and displaying the plant catalogue from get_plants.php.
 */

// --- Original Code ---

// Navbar scroll effect
const navbar = document.getElementById("navbar");
window.addEventListener("scroll", () => {
  if (window.scrollY > 50) {
    navbar.classList.add("scrolled");
  } else {
    navbar.classList.remove("scrolled");
  }
});

// Smooth scroll for navigation links
document.querySelectorAll('a[href^="#"]').forEach((anchor) => {
  anchor.addEventListener("click", function (e) {
    e.preventDefault();
    const target = document.querySelector(this.getAttribute("href"));
    if (target) {
      target.scrollIntoView({
        behavior: "smooth",
        block: "start",
      });
    }
  });
});

// Intersection Observer for feature cards animation
const observerOptions = {
  threshold: 0.1,
  rootMargin: "0px 0px -50px 0px",
};
const observer = new IntersectionObserver((entries) => {
  entries.forEach((entry) => {
    if (entry.isIntersecting) {
      const delay = entry.target.getAttribute("data-delay") || 0;
      setTimeout(() => {
        entry.target.classList.add("visible");
      }, delay);
      observer.unobserve(entry.target);
    }
  });
}, observerOptions);

document.querySelectorAll(".feature-card").forEach((card, index) => {
  card.setAttribute("data-delay", index * 100);
  observer.observe(card);
});


// --- NEW Code Starts Here ---

document.addEventListener('DOMContentLoaded', () => {

    // --- Modal Handling ---
    const modalOverlay = document.getElementById('modal-overlay');
    const signUpModal = document.getElementById('signUpModal');
    const signInModal = document.getElementById('signInModal');

    // Get all buttons that open modals
    const openSignUpButtons = [
        document.getElementById('getStartedBtn'),
        document.getElementById('heroGetStartedBtn'),
        document.getElementById('switchToSignUp')
    ];
    const openSignInButtons = [
        document.getElementById('signInBtn'),
        document.getElementById('switchToSignIn')
    ];

    // Get all buttons that close modals
    const closeButtons = [
        document.getElementById('closeSignUpBtn'),
        document.getElementById('closeSignInBtn'),
        modalOverlay
    ];

    const openModal = (modal) => {
        // Clear any previous messages
        document.getElementById('signUpMessage').textContent = '';
        document.getElementById('signInMessage').textContent = '';
        document.getElementById('signUpMessage').className = 'form-message';
        document.getElementById('signInMessage').className = 'form-message';

        modalOverlay.classList.add('visible');
        modal.classList.add('visible');
        document.body.classList.add('modal-open');
    };

    const closeModal = () => {
        modalOverlay.classList.remove('visible');
        if(signUpModal) signUpModal.classList.remove('visible');
        if(signInModal) signInModal.classList.remove('visible');
        document.body.classList.remove('modal-open');
    };

    openSignUpButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal(); // Close any open modal first
            openModal(signUpModal);
        });
    });

    openSignInButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal(); // Close any open modal first
            openModal(signInModal);
        });
    });

    closeButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            closeModal();
        });
    });


    // --- Auth Form Handling (Sign Up) ---
    const signUpForm = document.getElementById('signUpForm');
    const signUpMessage = document.getElementById('signUpMessage');

    signUpForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const username = document.getElementById('signUpUsername').value;
        const email = document.getElementById('signUpEmail').value;
        const location = document.getElementById('signUpLocation').value; // Get location
        const password = document.getElementById('signUpPassword').value;

        // Basic validation
        if (password.length < 8) {
            showMessage(signUpMessage, 'Password must be at least 8 characters.', 'error');
            return;
        }

        const formData = { username, email, password, location }; // Add location to form data

        try {
            // Send data to signup.php
            const response = await fetch('signup.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            const result = await response.json();

            if (result.success) {
                showMessage(signUpMessage, result.message, 'success');
                // After 2 seconds, close this modal and open the sign-in modal
                setTimeout(() => {
                    closeModal();
                    openModal(signInModal);
                }, 2000);
            } else {
                showMessage(signUpMessage, result.message, 'error');
            }
        } catch (error) {
            console.error('Sign-up error:', error);
            showMessage(signUpMessage, 'An unexpected error occurred. Please try again.', 'error');
        }
    });

    // --- Auth Form Handling (Sign In) ---
    const signInForm = document.getElementById('signInForm');
    const signInMessage = document.getElementById('signInMessage');

    signInForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const email = document.getElementById('signInEmail').value;
        const password = document.getElementById('signInPassword').value;
        const formData = { email, password };

        try {
            // Send data to signin.php
            const response = await fetch('signin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            const result = await response.json();

            if (result.success) {
                showMessage(signInMessage, result.message, 'success');
                // Update the UI to show logged-in state
                updateNavForLogin(result.username);
                // Close modal after 1.5 seconds
                setTimeout(closeModal, 1500);
            } else {
                showMessage(signInMessage, result.message, 'error');
            }
        } catch (error) {
            console.error('Sign-in error:', error);
            showMessage(signInMessage, 'An unexpected error occurred. Please try again.', 'error');
        }
    });

    // --- Logout ---
    document.getElementById('logoutBtn').addEventListener('click', () => {
        // In a real app, you'd call a logout.php script to destroy the session
        // For this demo, we'll just reset the UI
        document.getElementById('nav-auth-buttons').style.display = 'flex';
        document.getElementById('nav-user-details').style.display = 'none';
    });


    // --- Helper Functions ---
    function showMessage(element, message, type) {
        element.textContent = message;
        element.className = `form-message ${type}`; // 'success' or 'error'
    }

    function updateNavForLogin(username) {
        document.getElementById('nav-auth-buttons').style.display = 'none';
        document.getElementById('nav-user-details').style.display = 'flex';
        document.getElementById('usernameDisplay').textContent = `Hi, ${username}!`;
    }


    // --- Plant Catalogue Fetching ---
    const plantsGrid = document.getElementById('plants-grid-container');
    const loader = document.getElementById('plants-loader');

    async function fetchAndDisplayPlants() {
        if (!plantsGrid || !loader) return; // Only run if on the right page

        loader.classList.add('visible'); // Show loader
        
        try {
            const response = await fetch('get_plants.php');
            const data = await response.json();

            loader.classList.remove('visible'); // Hide loader

            if (data.success && data.plants) {
                plantsGrid.innerHTML = ''; // Clear loader/placeholder
                data.plants.forEach(plant => {
                    const plantCard = createPlantCard(plant);
                    plantsGrid.appendChild(plantCard);
                });
            } else {
                plantsGrid.innerHTML = '<p class="form-message error">Could not load plants.</p>';
            }
        } catch (error) {
            console.error('Error fetching plants:', error);
            loader.classList.remove('visible'); // Hide loader on error
            plantsGrid.innerHTML = '<p class="form-message error">An error occurred while fetching plants.</p>';
        }
    }

    function createPlantCard(plant) {
        // Create the card element
        const card = document.createElement('div');
        card.className = 'plant-card';

        // Set its inner HTML based on the data
        // This is safer than using innerHTML for the whole block
        card.innerHTML = `
            <div class="plant-image">
                <img src="${escapeHTML(plant.image_url)}" alt="${escapeHTML(plant.name)}">
                <div class="plant-badge">
                    <i class="${escapeHTML(plant.care_level_icon)}"></i>
                    ${escapeHTML(plant.care_level)}
                </div>
            </div>
            <div class="plant-content">
                <div class="plant-header">
                    <h3 class="plant-name">${escapeHTML(plant.name)}</h3>
                    <p class="plant-scientific">${escapeHTML(plant.scientific_name)}</p>
                </div>
                <div class="plant-info">
                    <div class="plant-info-item">
                        <i class="fas fa-droplet"></i>
                        <span>${escapeHTML(plant.watering)}</span>
                    </div>
                    <div class="plant-info-item">
                        <i class="fas fa-sun"></i>
                        <span>${escapeHTML(plant.light)}</span>
                    </div>
                </div>
                <button class="btn-plant">
                    View Care Guide
                    <i class="fas fa-arrow-right"></i>
                </button>
            </div>
        `;
        return card;
    }

    // Helper to prevent XSS attacks when inserting data
    function escapeHTML(str) {
        if (str === null || str === undefined) return '';
        return str.toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // --- Initial Load ---
    fetchAndDisplayPlants();

});