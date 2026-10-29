const registrationForm = document.getElementById('registrationForm');

if (registrationForm) {

    registrationForm.addEventListener('submit', function (event) {

        let isValid = true;


        // Email validation pattern
        const emailRegex =
            /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/;


        // Contact number validation pattern
        const phoneRegex =
            /^[0-9+\-\s]{7,15}$/;


        // Password must contain:
        // At least 8 characters
        // At least one lowercase letter
        // At least one uppercase letter
        // At least one number
        // At least one special character
        const passwordRegex =
            /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[^A-Za-z0-9]).{8,}$/;


        // Popular personal email domains
        const allowedDomains = [
            'gmail.com',
            'outlook.com',
            'hotmail.com',
            'yahoo.com',
            'icloud.com',
            'live.com',
            'protonmail.com',
            'aol.com',
            'mail.com',
            'zoho.com'
        ];


        // Get form fields
        const name = document.getElementById('name');

        const email = document.getElementById('email');

        const pass = document.getElementById('pass');

        const country = document.getElementById('country');

        const city = document.getElementById('city');

        const contact = document.getElementById('contact');


        // Clear previous error messages
        document.querySelectorAll('.field-error').forEach(function (error) {

            error.textContent = '';

        });


        // Full Name validation
        if (name.value.trim() === '') {

            document.getElementById('nameError').textContent =
                'Full name is required.';

            isValid = false;
        }


        // Email validation
        const enteredEmail =
            email.value.trim().toLowerCase();


        if (enteredEmail === '') {

            document.getElementById('emailError').textContent =
                'Email is required.';

            isValid = false;

        } else if (!emailRegex.test(enteredEmail)) {

            document.getElementById('emailError').textContent =
                'Enter a complete and valid email address.';

            isValid = false;

        } else {

            const emailParts =
                enteredEmail.split('@');

            const emailDomain =
                emailParts[1];


            // Check if it is a popular email provider
            const isPopularDomain =
                allowedDomains.includes(emailDomain);


            // Allow Ghanaian educational domains
            // such as ash esi.edu.gh, knust.edu.gh, ug.edu.gh
            const isSchoolDomain =
                emailDomain.endsWith('.edu.gh');


            if (!isPopularDomain && !isSchoolDomain) {

                document.getElementById('emailError').textContent =
                    'Please use a recognized email provider or a school email address ending in .edu.gh.';

                isValid = false;
            }
        }


        // Password validation
        if (pass.value === '') {

            document.getElementById('passError').textContent =
                'Password is required.';

            isValid = false;

        } else if (!passwordRegex.test(pass.value)) {

            document.getElementById('passError').textContent =
                'Password must be at least 8 characters and contain an uppercase letter, lowercase letter, number, and special character.';

            isValid = false;
        }


        // Country validation
        if (country.value === '') {

            document.getElementById('countryError').textContent =
                'Please select a country.';

            isValid = false;
        }


        // City validation
        if (city.value.trim() === '') {

            document.getElementById('cityError').textContent =
                'City is required.';

            isValid = false;
        }


        // Contact validation
        if (contact.value.trim() === '') {

            document.getElementById('contactError').textContent =
                'Contact number is required.';

            isValid = false;

        } else if (!phoneRegex.test(contact.value.trim())) {

            document.getElementById('contactError').textContent =
                'Enter a valid contact number.';

            isValid = false;
        }


        // Stop form submission if validation fails
        if (!isValid) {

            event.preventDefault();

            return;
        }


        // Loading state
        const registerButton =
            document.getElementById('registerButton');


        registerButton.disabled = true;

        registerButton.textContent =
            'Creating Account...';

    });

}
