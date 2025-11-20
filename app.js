/*
 * app.js (FINAL FULL WORKING VERSION)
 * Includes:
 * - Modal handling
 * - Signup / Signin API
 * - Plant catalogue loading with "Load More"
 * - Add to My Plants
 * - View Care Guide (Money Plant only)
 */

// --- Navbar Scroll Effect ---
const navbar = document.getElementById("navbar");
window.addEventListener("scroll", () => {
  if (window.scrollY > 50) navbar.classList.add("scrolled");
  else navbar.classList.remove("scrolled");
});

// --- Smooth Scroll ---
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
  anchor.addEventListener("click", (e) => {
    e.preventDefault();
    const target = document.querySelector(anchor.getAttribute("href"));
    if (target) target.scrollIntoView({ behavior: "smooth", block: "start" });
  });
});

// --- Feature Card Animations ---
const observerOptions = { threshold: 0.1, rootMargin: "0px 0px -50px 0px" };
const observer = new IntersectionObserver((entries) => {
  entries.forEach(entry => {
    if (entry.isIntersecting) {
      const delay = entry.target.getAttribute("data-delay") || 0;
      setTimeout(() => entry.target.classList.add("visible"), delay);
      observer.unobserve(entry.target);
    }
  });
}, observerOptions);

document.querySelectorAll(".feature-card").forEach((card, index) => {
  card.setAttribute("data-delay", index * 100);
  observer.observe(card);
});

// -------------------------
// START MAIN FUNCTIONALITY
// -------------------------
document.addEventListener("DOMContentLoaded", () => {

  // -------------------------
  // MODAL HANDLING
  // -------------------------
  const modalOverlay = document.getElementById('modal-overlay');
  const signUpModal = document.getElementById('signUpModal');
  const signInModal = document.getElementById('signInModal');

  const openSignUpButtons = [
    document.getElementById('getStartedBtn'),
    document.getElementById('heroGetStartedBtn'),
    document.getElementById('switchToSignUp')
  ];
  const openSignInButtons = [
    document.getElementById('signInBtn'),
    document.getElementById('switchToSignIn')
  ];
  const closeButtons = [
    document.getElementById('closeSignUpBtn'),
    document.getElementById('closeSignInBtn'),
    modalOverlay
  ];

  const openModal = (modal) => {
    const suMsg = document.getElementById('signUpMessage');
    const siMsg = document.getElementById('signInMessage');
    if (suMsg) suMsg.textContent = "";
    if (siMsg) siMsg.textContent = "";

    modalOverlay.classList.add('visible');
    modal.classList.add('visible');
    document.body.classList.add('modal-open');
  };

  const closeModal = () => {
    modalOverlay.classList.remove('visible');
    signUpModal.classList.remove('visible');
    signInModal.classList.remove('visible');
    document.body.classList.remove('modal-open');
  };

  openSignUpButtons.forEach(btn =>
    btn?.addEventListener("click", (e) => {
      e.preventDefault();
      closeModal();
      openModal(signUpModal);
    })
  );

  openSignInButtons.forEach(btn =>
    btn?.addEventListener("click", (e) => {
      e.preventDefault();
      closeModal();
      openModal(signInModal);
    })
  );

  closeButtons.forEach(btn =>
    btn?.addEventListener("click", (e) => {
      e.preventDefault();
      closeModal();
    })
  );

  // -------------------------
  // AUTH: SIGN UP
  // -------------------------
  const signUpForm = document.getElementById('signUpForm');
  const signUpMessage = document.getElementById('signUpMessage');

  if (signUpForm) {
    signUpForm.addEventListener("submit", async (e) => {
      e.preventDefault();

      const username = document.getElementById('signUpUsername').value;
      const email = document.getElementById('signUpEmail').value;
      const location = document.getElementById('signUpLocation').value;
      const password = document.getElementById('signUpPassword').value;

      if (password.length < 8) {
        showMessage(signUpMessage, "Password must be at least 8 characters.", "error");
        return;
      }

      try {
        const response = await fetch("signup.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ username, email, password, location })
        });

        const result = await response.json();

        if (result.success) {
          showMessage(signUpMessage, result.message, "success");
          setTimeout(() => { closeModal(); openModal(signInModal); }, 1500);
        } else {
          showMessage(signUpMessage, result.message, "error");
        }

      } catch (error) {
        showMessage(signUpMessage, "Unexpected error. Try again.", "error");
      }
    });
  }

  // -------------------------
  // AUTH: SIGN IN
  // -------------------------
  const signInForm = document.getElementById('signInForm');
  const signInMessage = document.getElementById('signInMessage');

  if (signInForm) {
    signInForm.addEventListener("submit", async (e) => {
      e.preventDefault();

      const email = document.getElementById('signInEmail').value;
      const password = document.getElementById('signInPassword').value;

      try {
        const response = await fetch("signin.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ email, password })
        });

        const result = await response.json();

        if (result.success) {
          showMessage(signInMessage, result.message, "success");
          updateNavForLogin(result.username);
          setTimeout(closeModal, 1200);
        } else {
          showMessage(signInMessage, result.message, "error");
        }

      } catch (error) {
        showMessage(signInMessage, "Unexpected error.", "error");
      }
    });
  }

  // Logout
  document.getElementById("logoutBtn")?.addEventListener("click", () => {
    document.getElementById("nav-auth-buttons").style.display = "flex";
    document.getElementById("nav-user-details").style.display = "none";
  });

  function showMessage(elem, msg, type) {
    if (!elem) return;
    elem.textContent = msg;
    elem.className = `form-message ${type}`;
  }

  function updateNavForLogin(username) {
    document.getElementById("nav-auth-buttons").style.display = "none";
    document.getElementById("nav-user-details").style.display = "flex";
    document.getElementById("usernameDisplay").textContent = `Hi, ${username}!`;
  }

  // -------------------------
  // PLANTS: LOAD WITH PAGINATION
  // -------------------------
  const plantsGrid = document.getElementById("plants-grid-container");
  const loader = document.getElementById("plants-loader");
  const loadMoreBtn = document.getElementById("loadMoreBtn");

  let allPlants = [];
  const plantsPerPage = 4;
  let currentIndex = 0;

  function displayNextPlants() {
    const slice = allPlants.slice(currentIndex, currentIndex + plantsPerPage);

    slice.forEach(plant => {
      plantsGrid.appendChild(createPlantCard(plant));
    });

    currentIndex += slice.length;

    if (currentIndex >= allPlants.length) loadMoreBtn.style.display = "none";
    else loadMoreBtn.style.display = "inline-flex";
  }

  async function fetchAndDisplayPlants() {
    loader.classList.add("visible");

    try {
      const response = await fetch("get_plants.php");
      const data = await response.json();

      loader.classList.remove("visible");

      if (data.success) {
        allPlants = data.plants;
        plantsGrid.innerHTML = "";
        currentIndex = 0;
        displayNextPlants();
      } else {
        plantsGrid.innerHTML = `<p class="form-message error">Could not load plants.</p>`;
      }

    } catch (err) {
      loader.classList.remove("visible");
      plantsGrid.innerHTML = `<p class="form-message error">Error loading plants.</p>`;
    }
  }

  function createPlantCard(plant) {
  const card = document.createElement("div");
  card.className = "plant-card";

  const guideLink = plant.name === "Money Plant"
    ? "care_money_plant.html"
    : "#"; // others do nothing

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
        <div class="plant-info-item"><i class="fas fa-droplet"></i> ${escapeHTML(plant.watering)}</div>
        <div class="plant-info-item"><i class="fas fa-sun"></i> ${escapeHTML(plant.light)}</div>
      </div>

      <button class="btn-plant-add" data-plant-id="${plant.id}">
        <i class="fas fa-plus"></i> Add to My Plants
      </button>

      <a href="${guideLink}" class="btn-plant">
        View Care Guide <i class="fas fa-arrow-right"></i>
      </a>
    </div>
  `;

  return card;
}



  function escapeHTML(str) {
    return (str || "").toString()
      .replace(/&/g, "&amp;").replace(/</g, "&lt;")
      .replace(/>/g, "&gt;").replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  // -----------------------------------------------------
  // CLICK HANDLERS (View Care Guide + Add to My Plants)
  // -----------------------------------------------------
  document.addEventListener("click", async (e) => {

    // View Care Guide
    const guideBtn = e.target.closest(".btn-view-guide");
    if (guideBtn) {
      const plantName = guideBtn.dataset.name;

      if (plantName === "Money Plant") {
        window.location.href = "care_money_plant.html";
      } else {
        alert("Care guide coming soon for: " + plantName);
      }
      return;
    }

    // Add to My Plants
    const addBtn = e.target.closest(".btn-plant-add");
    if (addBtn) {
      const plantId = addBtn.dataset.plantId;

      try {
        const response = await fetch("add_my_plant.php", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ plant_id: plantId })
        });

        const result = await response.json();
        alert(result.message);

      } catch (err) {
        alert("Could not add plant.");
      }
      return;
    }
  });

  // Load More
  loadMoreBtn?.addEventListener("click", (e) => {
    e.preventDefault();
    displayNextPlants();
  });

  // Initial load
  fetchAndDisplayPlants();
});
