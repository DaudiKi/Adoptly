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
