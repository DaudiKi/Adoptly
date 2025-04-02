(function(){
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        // Home Page Logic
        if (document.querySelector('.hero')) {
            console.log("Home page loaded.");
            // Additional home page-specific code can be added here.
        }
        
        // Gallery Page Logic
        if (document.querySelector('.gallery-grid')) {
            console.log("Gallery page loaded.");
            // Potential lightbox or carousel functionality can be added here.
        }
        
        // Sign-Up Page Logic
        var signupForm = document.getElementById('signupForm');
        if (signupForm) {
            signupForm.addEventListener('submit', function(e) {
                try {
                    // Clear previous error messages
                    document.querySelectorAll('.error').forEach(function(el) {
                        el.textContent = '';
                    });
                    var valid = true;
                    
                    // Validate full name (alphabetic only)
                    var fullName = document.getElementById('fullName').value.trim();
                    if (!/^[A-Za-z\s]+$/.test(fullName)) {
                        document.getElementById('nameError').textContent = "Please enter a valid name (alphabets only).";
                        valid = false;
                    }
                    
                    // Validate email
                    var email = document.getElementById('email').value.trim();
                    if (!email) {
                        document.getElementById('emailError').textContent = "Email is required.";
                        valid = false;
                    }
                    
                    // Validate phone number
                    var phone = document.getElementById('phone').value.trim();
                    if (!/^(\+?\d{1,3}[- ]?)?\d{10}$/.test(phone)) {
                        document.getElementById('phoneError').textContent = "Please enter a valid phone number.";
                        valid = false;
                    }
                    
                    // Validate date of birth
                    var dob = document.getElementById('dob').value;
                    if (!dob) {
                        document.getElementById('dobError').textContent = "Please enter a valid date.";
                        valid = false;
                    } else {
                        var dobDate = new Date(dob);
                        var today = new Date();
                        if (dobDate > today) {
                            document.getElementById('dobError').textContent = "Date cannot be in the future.";
                            valid = false;
                        }
                    }
                    
                    // Validate age/Year of Birth
                    var age = document.getElementById('age').value.trim();
                    if (!/^\d+$/.test(age)) {
                        document.getElementById('ageError').textContent = "Please enter numbers only.";
                        valid = false;
                    }
                    
                    // Validate account type selection
                    var accountType = document.getElementById('accountType').value;
                    if (!accountType) {
                        document.getElementById('accountError').textContent = "Please select an account type.";
                        valid = false;
                    }
                    
                    // Validate password strength
                    var password = document.getElementById('password').value;
                    var passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/;
                    if (!passwordRegex.test(password)) {
                        document.getElementById('passwordError').textContent = "Password must be at least 8 characters, include uppercase, lowercase, number, and special character.";
                        valid = false;
                    }
                    
                    // Confirm password match
                    var confirmPassword = document.getElementById('confirmPassword').value;
                    if (password !== confirmPassword) {
                        document.getElementById('confirmPasswordError').textContent = "Passwords do not match.";
                        valid = false;
                    }
                    
                    if (!valid) {
                        e.preventDefault();
                    } else {
                        alert("Sign up successful!");
                    }
                } catch(err) {
                    console.error("Error during form submission:", err);
                }
            });
        }
        
        // Unique Pet Matcher (Quiz) Page Logic
        var quizSubmit = document.getElementById('quizSubmit');
        if (quizSubmit) {
            quizSubmit.addEventListener('click', function() {
                try {
                    var petSize = document.getElementById('petSize').value;
                    var activityLevel = document.getElementById('activityLevel').value;
                    var indoorOutdoor = document.getElementById('indoorOutdoor').value;
                    
                    var result = "Based on your preferences, ";
                    
                    if (!petSize || !activityLevel || !indoorOutdoor) {
                        result = "Please answer all the questions to find your perfect pet match.";
                    } else {
                        if (petSize === "small" && activityLevel === "low" && indoorOutdoor === "indoor") {
                            result += "a calm and cuddly cat might be perfect for you!";
                        } else if (petSize === "large" && activityLevel === "high" && indoorOutdoor === "outdoor") {
                            result += "a playful dog would be a great match!";
                        } else {
                            result += "an adorable pet that suits your lifestyle is waiting for you!";
                        }
                    }
                    
                    document.getElementById('quizResult').textContent = result;
                } catch(err) {
                    console.error("Error during quiz evaluation:", err);
                }
            });
        }
    });
})();
// Enhanced Adoptly.js - Adding pet favorites and gallery functionality

document.addEventListener('DOMContentLoaded', function() {
    'use strict';
    
    // Common functions for all pages
    function showError(message) {
        const errorDiv = document.createElement('div');
        errorDiv.className = 'error-message';
        errorDiv.textContent = message;
        document.body.prepend(errorDiv);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            errorDiv.remove();
        }, 5000);
    }
    
    function showSuccess(message) {
        const successDiv = document.createElement('div');
        successDiv.className = 'success-message';
        successDiv.textContent = message;
        document.body.prepend(successDiv);
        
        // Auto-remove after 5 seconds
        setTimeout(() => {
            successDiv.remove();
        }, 5000);
    }
    
    // Back to top functionality
    const backToTopButton = document.querySelector('.back-to-top');
    if (backToTopButton) {
        backToTopButton.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Show/hide button based on scroll position
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.style.display = 'block';
            } else {
                backToTopButton.style.display = 'none';
            }
        });
    }
    
    // Home Page specific code
    if (document.querySelector('.hero')) {
        console.log("Home page loaded.");
        
        // Search functionality
        const searchForm = document.getElementById('searchForm');
        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const searchInput = document.getElementById('searchInput').value.trim().toLowerCase();
                const petTypes = document.querySelectorAll('input[name="petType"]:checked');
                
                let searchParams = new URLSearchParams();
                if (searchInput) {
                    searchParams.append('search', searchInput);
                }
                
                let typeParams = [];
                petTypes.forEach(type => {
                    typeParams.push(type.value);
                });
                
                if (typeParams.length > 0) {
                    searchParams.append('types', typeParams.join(','));
                }
                
                // Redirect to gallery with search parameters
                window.location.href = 'AdoptlyGallery.php?' + searchParams.toString();
            });
        }
    }
    
    // Gallery Page specific code
    if (document.querySelector('.gallery-grid')) {
        console.log("Gallery page loaded.");
        
        // Parse URL parameters for filtering
        const urlParams = new URLSearchParams(window.location.search);
        const searchTerm = urlParams.get('search');
        const petTypes = urlParams.get('types') ? urlParams.get('types').split(',') : [];
        
        // Apply filters if present
        if (searchTerm || petTypes.length > 0) {
            const galleryItems = document.querySelectorAll('.gallery-item');
            
            galleryItems.forEach(item => {
                let visible = true;
                
                // Filter by pet type
                if (petTypes.length > 0) {
                    const itemType = item.getAttribute('data-category');
                    if (!petTypes.includes(itemType)) {
                        visible = false;
                    }
                }
                
                // Filter by search term
                if (searchTerm && visible) {
                    const petInfo = item.textContent.toLowerCase();
                    if (!petInfo.includes(searchTerm.toLowerCase())) {
                        visible = false;
                    }
                }
                
                // Show/hide based on filters
                item.style.display = visible ? 'block' : 'none';
            });
            
            // Show filter notice
            const filterContainer = document.querySelector('.filter-container');
            if (filterContainer) {
                let filterText = 'Showing pets';
                
                if (petTypes.length > 0) {
                    filterText += ' of type: ' + petTypes.map(type => type.charAt(0).toUpperCase() + type.slice(1)).join(', ');
                }
                
                if (searchTerm) {
                    filterText += ' matching: "' + searchTerm + '"';
                }
                
                const filterNotice = document.createElement('div');
                filterNotice.className = 'filter-notice';
                filterNotice.innerHTML = filterText + ' <a href="AdoptlyGallery.php">Clear filters</a>';
                filterContainer.appendChild(filterNotice);
            }
        }
        
        // Pet modal functionality
        const petModal = document.getElementById('pet-modal');
        const modalDetails = document.getElementById('modal-pet-details');
        const closeModal = document.querySelector('.close-modal');
        const petDetailsBtns = document.querySelectorAll('.pet-details-btn');
        
        if (petModal && modalDetails && closeModal && petDetailsBtns.length > 0) {
            // Open modal when clicking "View Details"
            petDetailsBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const petCard = this.closest('.pet-card');
                    const petName = petCard.querySelector('h3').textContent;
                    const petBreed = petCard.querySelector('.pet-breed').textContent;
                    const petAge = petCard.querySelector('.pet-age').textContent;
                    const petDesc = petCard.querySelector('.pet-description').textContent;
                    const petImg = petCard.querySelector('img').getAttribute('src');
                    
                    // Create detailed view
                    modalDetails.innerHTML = `
                        <div class="pet-detail-view">
                            <h2>${petName}</h2>
                            <img src="${petImg}" alt="${petName}" class="pet-detail-img">
                            <div class="pet-detail-info">
                                <p><strong>Breed:</strong> ${petBreed}</p>
                                <p><strong>Age:</strong> ${petAge}</p>
                                <p><strong>Description:</strong> ${petDesc}</p>
                                <button class="adopt-btn">Start Adoption Process</button>
                            </div>
                        </div>
                    `;
                    
                    // Attach adoption button event
                    const adoptBtn = modalDetails.querySelector('.adopt-btn');
                    if (adoptBtn) {
                        adoptBtn.addEventListener('click', function() {
                            // Check if user is logged in (by checking for a login/logout link)
                            const logoutLink = document.querySelector('a[href="logout.php"]');
                            if (!logoutLink) {
                                alert('Please log in or sign up to begin the adoption process.');
                                window.location.href = 'login.php';
                            } else {
                                // Redirect to adoption form or show next steps
                                alert(`Thank you for your interest in adopting ${petName}! Our team will contact you shortly to proceed with the adoption process.`);
                                petModal.style.display = 'none';
                            }
                        });
                    }
                    
                    // Show the modal
                    petModal.style.display = 'block';
                });
            });
            
            // Close modal functionality
            closeModal.addEventListener('click', function() {
                petModal.style.display = 'none';
            });
            
            // Close modal when clicking outside
            window.addEventListener('click', function(e) {
                if (e.target == petModal) {
                    petModal.style.display = 'none';
                }
            });
        }
        
        // Favorites functionality
        const favoriteBtns = document.querySelectorAll('.favorite-btn');
        const favoritesSection = document.getElementById('favorites-section');
        
        if (favoriteBtns.length > 0) {
            favoriteBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const petId = this.getAttribute('data-pet-id');
                    const petCard = this.closest('.pet-card');
                    const petName = petCard.querySelector('h3').textContent;
                    
                    // Check if user is logged in (by checking for a login/logout link)
                    const logoutLink = document.querySelector('a[href="logout.php"]');
                    if (!logoutLink) {
                        alert('Please log in or sign up to add pets to your favorites.');
                        window.location.href = 'login.php';
                        return;
                    }
                    
                    // AJAX call to add/remove from favorites
                    const xhr = new XMLHttpRequest();
                    xhr.open('POST', 'add_favorite.php', true);
                    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
                    xhr.onload = function() {
                        if (xhr.status === 200) {
                            try {
                                const response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    // Update button text
                                    if (response.action === 'added') {
                                        btn.textContent = 'Remove from Favorites';
                                        btn.classList.add('favorited');
                                        showSuccess(`${petName} added to your favorites!`);
                                    } else {
                                        btn.textContent = 'Add to Favorites';
                                        btn.classList.remove('favorited');
                                        showSuccess(`${petName} removed from your favorites.`);
                                    }
                                    
                                    // Update favorites section if present
                                    if (favoritesSection && response.favorites) {
                                        updateFavoritesSection(response.favorites);
                                    }
                                } else {
                                    showError(response.message || 'Error updating favorites.');
                                }
                            } catch (e) {
                                showError('Error processing response.');
                            }
                        } else {
                            showError('Error connecting to server.');
                        }
                    };
                    xhr.onerror = function() {
                        showError('Request failed.');
                    };
                    xhr.send('pet_id=' + petId);
                });
            });
        }
        
        // Function to update favorites section
        function updateFavoritesSection(favorites) {
            if (!favoritesSection) return;
            
            // Clear current favorites
            favoritesSection.innerHTML = '';
            
            if (favorites.length === 0) {
                favoritesSection.innerHTML = '<p>You have no favorite pets yet.</p>';
                return;
            }
            
            // Add heading
            const heading = document.createElement('h3');
            heading.textContent = 'Your Favorite Pets';
            favoritesSection.appendChild(heading);
            
            // Create favorites grid
            const grid = document.createElement('div');
            grid.className = 'favorites-grid';
            
            favorites.forEach(pet => {
                const item = document.createElement('div');
                item.className = 'favorite-item';
                item.innerHTML = `
                    <img src="${pet.image_url}" alt="${pet.name}">
                    <div class="favorite-info">
                        <h4>${pet.name}</h4>
                        <p>${pet.breed}</p>
                        <button class="view-favorite" data-pet-id="${pet.pet_id}">View Details</button>
                        <button class="remove-favorite" data-pet-id="${pet.pet_id}">Remove</button>
                    </div>
                `;
                grid.appendChild(item);
            });
            
            favoritesSection.appendChild(grid);
            
            // Attach event listeners to new buttons
            const viewBtns = favoritesSection.querySelectorAll('.view-favorite');
            viewBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const petId = this.getAttribute('data-pet-id');
                    // Find the corresponding pet details button and trigger it
                    const detailBtn = document.querySelector(`.pet-details-btn[data-pet-id="${petId}"]`);
                    if (detailBtn) {
                        detailBtn.click();
                    }
                });
            });
            
            const removeBtns = favoritesSection.querySelectorAll('.remove-favorite');
            removeBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const petId = this.getAttribute('data-pet-id');
                    // Find the corresponding favorite button and trigger it
                    const favoriteBtn = document.querySelector(`.favorite-btn[data-pet-id="${petId}"]`);
                    if (favoriteBtn) {
                        favoriteBtn.click();
                    }
                });
            });
        }
        
        // Check user's favorites on page load
        function loadUserFavorites() {
            // Only proceed if user is logged in
            const logoutLink = document.querySelector('a[href="logout.php"]');
            if (!logoutLink || !favoritesSection) return;
            
            const xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_favorites.php', true);
            xhr.onload = function() {
                if (xhr.status === 200) {
                    try {
                        const response = JSON.parse(xhr.responseText);
                        if (response.success && response.favorites) {
                            // Mark favorite buttons
                            response.favorites.forEach(pet => {
                                const btn = document.querySelector(`.favorite-btn[data-pet-id="${pet.pet_id}"]`);
                                if (btn) {
                                    btn.textContent = 'Remove from Favorites';
                                    btn.classList.add('favorited');
                                }
                            });
                            
                            // Update favorites section
                            updateFavoritesSection(response.favorites);
                        }
                    } catch (e) {
                        console.error('Error parsing favorites response:', e);
                    }
                }
            };
            xhr.send();
        }
        
        // Load favorites on page load
        loadUserFavorites();
    }
    
    // Sign-Up Page specific code
    const signupForm = document.getElementById('signupForm');
    if (signupForm) {
        console.log("Sign-up page loaded.");
        
        // Account type field change handler
        const accountType = document.getElementById('accountType');
        if (accountType) {
            accountType.addEventListener('change', function() {
                const adopterFields = document.getElementById('adopterFields');
                const volunteerFields = document.getElementById('volunteerFields');
                const rescuerFields = document.getElementById('rescuerFields');
                
                // Hide all account-specific fields
                if (adopterFields) adopterFields.style.display = 'none';
                if (volunteerFields) volunteerFields.style.display = 'none';
                if (rescuerFields) rescuerFields.style.display = 'none';
                
                // Show the relevant fields
                const selectedType = this.value;
                if (selectedType === 'adopter' && adopterFields) {
                    adopterFields.style.display = 'block';
                } else if (selectedType === 'volunteer' && volunteerFields) {
                    volunteerFields.style.display = 'block';
                } else if (selectedType === 'rescuer' && rescuerFields) {
                    rescuerFields.style.display = 'block';
                }
            });
        }
        
        // Terms and conditions modal
        const termsLink = document.querySelector('.terms-link');
        const termsModal = document.getElementById('terms-modal');
        if (termsLink && termsModal) {
            termsLink.addEventListener('click', function(e) {
                e.preventDefault();
                termsModal.style.display = 'block';
            });
            
            const closeModal = termsModal.querySelector('.close-modal');
            if (closeModal) {
                closeModal.addEventListener('click', function() {
                    termsModal.style.display = 'none';
                });
            }
            
            window.addEventListener('click', function(e) {
                if (e.target == termsModal) {
                    termsModal.style.display = 'none';
                }
            });
        }
        
        // Client-side form validation is already handled in the original Adoptly.js
        // This enhanced version just adds the missing functionality
    }
    
    // Quiz (Pet Matcher) functionality
    const quizSubmit = document.getElementById('quizSubmit');
    if (quizSubmit) {
        console.log("Pet Matcher page loaded.");
        // The quiz functionality is already handled in the original Adoptly.js
    }
});