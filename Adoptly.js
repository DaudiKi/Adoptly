(function(){
  'use strict';

  document.addEventListener('DOMContentLoaded', function() {
    // ======== Common UI Elements ========

    // Mobile Navigation Toggle
    const createMobileMenu = () => {
      const header = document.querySelector('header');
      const nav = document.querySelector('nav');

      if (header && nav) {
        const menuToggle = document.createElement('button');
        menuToggle.className = 'mobile-menu-toggle';
        menuToggle.innerHTML = '&#9776;'; // hamburger icon
        menuToggle.setAttribute('aria-label', 'Toggle navigation menu');

        menuToggle.addEventListener('click', () => {
          const navList = nav.querySelector('ul');
          navList.classList.toggle('show');
        });

        header.insertBefore(menuToggle, nav);
      }
    };

    // Back to Top Button
    const createBackToTopButton = () => {
      const backToTopBtn = document.querySelector('.back-to-top');

      if (backToTopBtn) {
        backToTopBtn.addEventListener('click', () => {
          window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        window.addEventListener('scroll', () => {
          if (window.scrollY > 300) {
            backToTopBtn.classList.add('visible');
          } else {
            backToTopBtn.classList.remove('visible');
          }
        });
      }
    };

    // Initialize common elements
    createMobileMenu();
    createBackToTopButton();

    // ======== Home Page Logic ========
    if (document.querySelector('.hero')) {
      console.log("Home page loaded.");

      // Animate featured pets on scroll
      const featuredItems = document.querySelectorAll('.featured-item');
      if (featuredItems.length) {
        const observerOptions = {
          threshold: 0.1,
          rootMargin: '0px 0px -50px 0px'
        };

        const appearOnScroll = new IntersectionObserver((entries, observer) => {
          entries.forEach(entry => {
            if (entry.isIntersecting) {
              entry.target.style.opacity = 1;
              entry.target.style.transform = 'translateY(0)';
              observer.unobserve(entry.target);
            }
          });
        }, observerOptions);

        featuredItems.forEach(item => {
          item.style.opacity = 0;
          item.style.transform = 'translateY(30px)';
          item.style.transition = 'opacity 0.6s ease-out, transform 0.6s ease-out';
          appearOnScroll.observe(item);
        });
      }

      // Search Form Logic
      const searchForm = document.getElementById('searchForm');
      if (searchForm) {
        searchForm.addEventListener('submit', function(e) {
          e.preventDefault();
          const searchInput = document.getElementById('searchInput').value.toLowerCase();
          const petTypes = Array.from(document.querySelectorAll('input[name="petType"]:checked'))
            .map(input => input.value);

          // Save search parameters to session storage for use in the Gallery page
          sessionStorage.setItem('adoptlySearch', JSON.stringify({
            term: searchInput,
            types: petTypes
          }));

          // Redirect to Gallery page
          window.location.href = 'AdoptlyGallery.html';
        });
      }
    }

    // ======== Gallery Page Logic ========
    if (document.querySelector('.gallery-grid')) {
      console.log("Gallery page loaded.");

      // Create the modal for pet details
      const setupModal = () => {
        const modalElements = document.querySelectorAll('.modal');

        modalElements.forEach(modal => {
          const closeBtn = modal.querySelector('.close-modal');
          if (closeBtn) {
            closeBtn.addEventListener('click', () => {
              modal.classList.remove('show');
              document.body.style.overflow = '';
            });
          }

          // Close if clicked outside the modal content
          modal.addEventListener('click', (e) => {
            if (e.target === modal) {
              modal.classList.remove('show');
              document.body.style.overflow = '';
            }
          });
        });
      };

      // Initialize modals
      setupModal();

      // Add filter functionality
      const createFilterButtons = () => {
        const filterContainer = document.querySelector('.filter-container');
        if (!filterContainer) return;

        const filterOptions = [
          { label: 'All Pets', value: 'all' },
          { label: 'Dogs', value: 'dog' },
          { label: 'Cats', value: 'cat' },
          { label: 'Other Pets', value: 'other' }
        ];

        filterOptions.forEach(option => {
          const button = document.createElement('button');
          button.className = 'filter-btn';
          button.textContent = option.label;
          button.setAttribute('data-filter', option.value);

          if (option.value === 'all') {
            button.classList.add('active');
          }

          button.addEventListener('click', (e) => {
            // Remove active class from all buttons
            document.querySelectorAll('.filter-btn').forEach(btn => {
              btn.classList.remove('active');
            });

            // Add active class to clicked button
            e.target.classList.add('active');

            // Filter gallery items
            const filter = e.target.getAttribute('data-filter');
            filterGalleryItems(filter);
          });

          filterContainer.appendChild(button);
        });

        // Check for search parameters in session storage
        const searchParams = sessionStorage.getItem('adoptlySearch');
        if (searchParams) {
          try {
            const params = JSON.parse(searchParams);
            if (params.types && params.types.length === 1) {
              // Activate the filter button for the type
              const filterBtn = document.querySelector(`.filter-btn[data-filter="${params.types[0]}"]`);
              if (filterBtn) {
                filterBtn.click();
              }
            }

            // Create search term notification
            if (params.term) {
              const searchNotification = document.createElement('div');
              searchNotification.className = 'alert alert-info';
              searchNotification.textContent = `Showing results for: "${params.term}"`;
              filterContainer.parentNode.insertBefore(searchNotification, filterContainer);
            }

            // Clear search parameters after use
            sessionStorage.removeItem('adoptlySearch');
          } catch (err) {
            console.error("Error parsing search parameters:", err);
          }
        }
      };

      const filterGalleryItems = (filter) => {
        const galleryItems = document.querySelectorAll('.gallery-item');

        galleryItems.forEach(item => {
          if (filter === 'all') {
            item.style.display = 'block';
          } else {
            const itemCategory = item.getAttribute('data-category');
            if (itemCategory === filter) {
              item.style.display = 'block';
            } else {
              item.style.display = 'none';
            }
          }
        });
      };

      // Initialize filters
      createFilterButtons();

      // Pet Details Modal
      const setupPetDetails = () => {
        const detailButtons = document.querySelectorAll('.pet-details-btn');
        const modal = document.getElementById('pet-modal');
        const modalContent = document.getElementById('modal-pet-details');

        detailButtons.forEach(button => {
          button.addEventListener('click', function() {
            const petCard = this.closest('.pet-card');
            const petName = petCard.querySelector('h3').textContent;
            const petBreed = petCard.querySelector('.pet-breed').textContent;
            const petAge = petCard.querySelector('.pet-age').textContent;
            const petDesc = petCard.querySelector('.pet-description').textContent;
            const petImg = petCard.querySelector('img').src;

            modalContent.innerHTML = `
                            <div class="pet-details">
                                <div class="pet-details-header">
                                    <h3>${petName}</h3>
                                </div>
                                <img src="${petImg}" alt="${petName}">
                                <div class="pet-details-info">
                                    <p><strong>Breed:</strong> ${petBreed}</p>
                                    <p><strong>Age:</strong> ${petAge}</p>
                                    <p><strong>Description:</strong> ${petDesc}</p>
                                </div>
                                <div class="pet-actions">
                                    <button class="btn btn-primary">Adopt Me</button>
                                </div>
                            </div>
                        `;

            modal.classList.add('show');
            document.body.style.overflow = 'hidden';
          });
        });
      };

      // Favorites functionality
      const setupFavorites = () => {
        // Load favorites from localStorage
        let favorites = [];
        const savedFavorites = localStorage.getItem('adoptlyFavorites');

        if (savedFavorites) {
          try {
            favorites = JSON.parse(savedFavorites);
          } catch (err) {
            console.error("Error loading favorites:", err);
          }
        }

        // Update favorite buttons to reflect current state
        const favoriteButtons = document.querySelectorAll('.favorite-btn');
        favoriteButtons.forEach(button => {
          const petId = button.getAttribute('data-pet-id');

          if (favorites.includes(petId)) {
            button.textContent = 'Remove from Favorites';
            button.classList.add('active');
          }

          button.addEventListener('click', function() {
            const petId = this.getAttribute('data-pet-id');
            const petCard = this.closest('.pet-card');
            const petName = petCard.querySelector('h3').textContent;

            if (favorites.includes(petId)) {
              // Remove from favorites
              favorites = favorites.filter(id => id !== petId);
              this.textContent = 'Add to Favorites';
              this.classList.remove('active');

              // Show notification
              showNotification(`${petName} removed from favorites`, 'info');
            } else {
              // Add to favorites
              favorites.push(petId);
              this.textContent = 'Remove from Favorites';
              this.classList.add('active');

              // Show notification
              showNotification(`${petName} added to favorites`, 'success');
            }

            // Save to localStorage
            localStorage.setItem('adoptlyFavorites', JSON.stringify(favorites));

            // Update favorites section
            updateFavoritesSection();
          });
        });

        // Create the favorites section if we have favorites
        updateFavoritesSection();

        function updateFavoritesSection() {
          const favoritesSection = document.getElementById('favorites-section');

          if (favorites.length === 0) {
            favoritesSection.innerHTML = '';
            return;
          }

          let html = `
                        <h3>Your Favorite Pets</h3>
                        <div class="favorites-grid">
                    `;

          // Get all pet cards to extract info
          const petCards = document.querySelectorAll('.pet-card');

          petCards.forEach(card => {
            const favoriteBtn = card.querySelector('.favorite-btn');
            if (!favoriteBtn) return;

            const petId = favoriteBtn.getAttribute('data-pet-id');

            if (favorites.includes(petId)) {
              const petName = card.querySelector('h3').textContent;
              const petImg = card.querySelector('img').src;

              html += `
                                <div class="favorite-pet" data-pet-id="${petId}">
                                    <img src="${petImg}" alt="${petName}">
                                    <p>${petName}</p>
                                </div>
                            `;
            }
          });

          html += `</div>`;
          favoritesSection.innerHTML = html;

          // Add click event to favorite pets
          const favoritePets = document.querySelectorAll('.favorite-pet');
          favoritePets.forEach(pet => {
            pet.addEventListener('click', function() {
              const petId = this.getAttribute('data-pet-id');
              const detailBtn = document.querySelector(`.favorite-btn[data-pet-id="${petId}"]`)
                .closest('.pet-card')
                .querySelector('.pet-details-btn');

              detailBtn.click();
            });
          });
        }
      };

      // Function to show notifications
      function showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type}`;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.zIndex = '1000';
        notification.style.maxWidth = '300px';
        notification.style.opacity = '0';
        notification.style.transform = 'translateY(-20px)';
        notification.style.transition = 'opacity 0.3s, transform 0.3s';
        notification.textContent = message;

        document.body.appendChild(notification);

        // Trigger animation
        setTimeout(() => {
          notification.style.opacity = '1';
          notification.style.transform = 'translateY(0)';
        }, 10);

        // Remove after 3 seconds
        setTimeout(() => {
          notification.style.opacity = '0';
          notification.style.transform = 'translateY(-20px)';

          // Remove from DOM after transition
          setTimeout(() => {
            document.body.removeChild(notification);
          }, 300);
        }, 3000);
      }

      // Initialize pet details and favorites
      setupPetDetails();
      setupFavorites();
    }

    // ======== Sign-Up Page Logic ========
    var signupForm = document.getElementById('signupForm');
    if (signupForm) {
      // Account type fields toggle
      const accountTypeSelect = document.getElementById('accountType');
      const accountFields = {
        'adopter': document.getElementById('adopterFields'),
        'volunteer': document.getElementById('volunteerFields'),
        'rescuer': document.getElementById('rescuerFields')
      };

      if (accountTypeSelect && Object.values(accountFields).some(field => field !== null)) {
        accountTypeSelect.addEventListener('change', function() {
          // Hide all account-specific fields
          Object.values(accountFields).forEach(field => {
            if (field) field.classList.remove('show');
          });

          // Show the selected account type fields
          const selectedType = this.value;
          if (selectedType && accountFields[selectedType]) {
            accountFields[selectedType].classList.add('show');
          }
        });
      }

      // Terms and conditions modal
      const termsLink = document.querySelector('.terms-link');
      const termsModal = document.getElementById('terms-modal');

      if (termsLink && termsModal) {
        termsLink.addEventListener('click', function(e) {
          e.preventDefault();
          termsModal.classList.add('show');
          document.body.style.overflow = 'hidden';
        });

        const closeBtn = termsModal.querySelector('.close-modal');
        if (closeBtn) {
          closeBtn.addEventListener('click', () => {
            termsModal.classList.remove('show');
            document.body.style.overflow = '';
          });
        }

        // Close if clicked outside the modal content
        termsModal.addEventListener('click', (e) => {
          if (e.target === termsModal) {
            termsModal.classList.remove('show');
            document.body.style.overflow = '';
          }
        });
      }

      // Real-time validation
      const inputs = signupForm.querySelectorAll('input, select');

      inputs.forEach(input => {
        input.addEventListener('input', function() {
          validateInput(this);
        });

        input.addEventListener('blur', function() {
          validateInput(this, true);
        });
      });

      // Password strength meter
      const passwordInput = document.getElementById('password');
      if (passwordInput) {
        // Create password strength meter
        const strengthMeter = document.createElement('div');
        strengthMeter.className = 'password-strength';
        const meterBar = document.createElement('div');
        meterBar.className = 'password-strength-meter';
        strengthMeter.appendChild(meterBar);

        // Insert after password input
        passwordInput.parentNode.insertBefore(strengthMeter, document.getElementById('passwordError'));

        // Update strength meter on input
        passwordInput.addEventListener('input', function() {
          updatePasswordStrength(this.value, meterBar);
        });
      }

      signupForm.addEventListener('submit', function(e) {
        e.preventDefault();

        // Clear previous error messages
        document.querySelectorAll('.error').forEach(function(el) {
          el.textContent = '';
        });

        // Validate all fields
        let valid = true;
        inputs.forEach(input => {
          if (!validateInput(input, true)) {
            valid = false;
          }
        });

        if (valid) {
          // Show success message
          const successMessage = document.createElement('div');
          successMessage.className = 'alert alert-success';
          successMessage.innerHTML = `
                        <strong>Sign up successful!</strong><br>
                        Thank you for joining Adoptly. You can now <a href="AdoptlyGallery.html">browse our pet gallery</a> or <a href="AdoptlyQuiz.html">take our pet matcher quiz</a>.
                    `;

          signupForm.parentNode.insertBefore(successMessage, signupForm);
          signupForm.style.display = 'none';

          // Scroll to the success message
          successMessage.scrollIntoView({ behavior: 'smooth' });
        }
      });

      // Function to validate a single input
      function validateInput(input, showError = false) {
        let valid = true;
        let errorMsg = '';

        switch (input.id) {
          case 'fullName':
            if (!/^[A-Za-z\s]+$/.test(input.value.trim())) {
              errorMsg = "Please enter a valid name (alphabets only).";
              valid = false;
            }
            break;

          case 'email':
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value.trim())) {
              errorMsg = "Please enter a valid email address.";
              valid = false;
            }
            break;

          case 'phone':
            if (!/^(\+?\d{1,3}[- ]?)?\d{10}$/.test(input.value.trim())) {
              errorMsg = "Please enter a valid 10-digit phone number.";
              valid = false;
            }
            break;

          case 'dob':
            if (!input.value) {
              errorMsg = "Please enter your date of birth.";
              valid = false;
            } else {
              const dobDate = new Date(input.value);
              const today = new Date();
              const age = today.getFullYear() - dobDate.getFullYear();

              if (dobDate > today) {
                errorMsg = "Date cannot be in the future.";
                valid = false;
              } else if (age < 18) {
                errorMsg = "You must be at least 18 years old to adopt.";
                valid = false;
              }
            }
            break;

          case 'accountType':
            if (!input.value) {
              errorMsg = "Please select an account type.";
              valid = false;
            }
            break;

          case 'password':
            const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
            if (!passwordRegex.test(input.value)) {
              errorMsg = "Password must be at least 8 characters with uppercase, lowercase, number, and special character.";
              valid = false;
            }
            break;

          case 'confirmPassword':
            const password = document.getElementById('password').value;
            if (input.value !== password) {
              errorMsg = "Passwords do not match.";
              valid = false;
            }
            break;

          case 'terms':
            if (!input.checked) {
              errorMsg = "You must agree to the terms and conditions.";
              valid = false;
            }
            break;

          // Account type specific validations
          case 'housingType':
            if (document.getElementById('accountType').value === 'adopter' && !input.value) {
              errorMsg = "Please select your housing type.";
              valid = false;
            }
            break;

          case 'availability':
            if (document.getElementById('accountType').value === 'volunteer' && !input.value) {
              errorMsg = "Please select your availability.";
              valid = false;
            }
            break;

          case 'experience':
            if (document.getElementById('accountType').value === 'rescuer' && !input.value) {
              errorMsg = "Please enter your years of experience.";
              valid = false;
            }
            break;
        }

        // Show or clear error message
        const errorElement = document.getElementById(input.id + 'Error');
        if (errorElement) {
          errorElement.textContent = showError ? errorMsg : '';
        }

        // Visual feedback
        if (input.type !== 'checkbox') {
          if (valid && input.value !== '') {
            input.style.borderColor = '#2ecc71';
          } else if (!valid && showError) {
            input.style.borderColor = '#e74c3c';
          } else {
            input.style.borderColor = '#ccc';
          }
        }

        return valid;
      }

      // Function to update password strength meter
      function updatePasswordStrength(password, meterElement) {
        // Remove existing classes
        meterElement.classList.remove('strength-weak', 'strength-medium', 'strength-strong');

        if (!password) {
          meterElement.style.width = '0';
          return;
        }

        let strength = 0;

        // Length check
        if (password.length >= 8) strength += 1;

        // Character type checks
        if (/[A-Z]/.test(password)) strength += 1;
        if (/[a-z]/.test(password)) strength += 1;
        if (/[0-9]/.test(password)) strength += 1;
        if (/[\W_]/.test(password)) strength += 1;

        // Update meter
        if (strength <= 2) {
          meterElement.classList.add('strength-weak');
        } else if (strength <= 4) {
          meterElement.classList.add('strength-medium');
        } else {
          meterElement.classList.add('strength-strong');
        }
      }
    }
