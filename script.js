document.addEventListener("DOMContentLoaded", function () {

    /* ---------- Complaint form validation ---------- */
    const complaintForm = document.getElementById("complaintForm");

    if (complaintForm) {
        complaintForm.addEventListener("submit", function (e) {
            let valid = true;

            const name = document.getElementById("customer_name");
            const email = document.getElementById("customer_email");
            const subject = document.getElementById("subject");
            const message = document.getElementById("message");

            valid = validateField(name, name.value.trim().length >= 2,
                "Please enter your full name.") && valid;

            valid = validateField(email, isValidEmail(email.value.trim()),
                "Please enter a valid email address.") && valid;

            valid = validateField(subject, subject.value.trim().length >= 3,
                "Please enter a subject.") && valid;

            valid = validateField(message, message.value.trim().length >= 10,
                "Please describe your issue (at least 10 characters).") && valid;

            if (!valid) {
                e.preventDefault();
            }
        });
    }

    /* ---------- Admin Login form validation ---------- */
    const loginForm = document.getElementById("adminLoginForm");

    if (loginForm) {
        loginForm.addEventListener("submit", function (e) {
            let valid = true;

            const username = document.getElementById("username");
            const password = document.getElementById("password");

            valid = validateField(username, username.value.trim().length > 0,
                "Username is required.") && valid;

            valid = validateField(password, password.value.length > 0,
                "Password is required.") && valid;

            if (!valid) {
                e.preventDefault();
            }
        });
    }

    /* ---------- Status dropdown change ---------- */
    const statusSelects = document.querySelectorAll(".action-select");
    statusSelects.forEach(function (select) {
        select.addEventListener("change", function () {
            if (confirm("Update the status for this complaint?")) {
                select.form.submit();
            }
        });
    });

    /* ---------- Add to Cart (With Login Check) ---------- */
    document.querySelectorAll('.btn-add-cart').forEach(button => {
        button.addEventListener('click', (e) => {
            e.preventDefault();

            // 1. Client-Side Login Check (Optional fast check via body dataset)
            const isUserLoggedIn = document.body.getAttribute('data-logged-in') === 'true';

            if (!isUserLoggedIn) {
                alert("Please log in first to add items to your cart.");
                window.location.href = "login.php"; // Redirect to login page
                return;
            }

            const card = e.target.closest('.product-card');
            if (!card) return;

            const productId = card.getAttribute('data-id') || 0;
            const productName = card.querySelector('.product-name')?.textContent.trim() || '';
            const qtyElement = card.querySelector('.qty-val');
            const quantity = qtyElement ? (parseInt(qtyElement.textContent, 10) || 1) : 1;
            const rawPrice = card.querySelector('.price')?.textContent || '0';
            const productPrice = parseFloat(rawPrice.replace(/[^0-9.]/g, '')) || 0;
            const imgElement = card.querySelector('.product-image');
            const productImage = imgElement ? imgElement.getAttribute('src') : '';

            const formData = new URLSearchParams();
            formData.append('product_id', productId);
            formData.append('product_name', productName);
            formData.append('product_price', productPrice);
            formData.append('quantity', quantity);
            formData.append('product_image', productImage);

            // 2. Server-Side Verification
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: formData.toString()
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert(`✓ Successfully added ${quantity} x ${productName} to your cart!`);
                } else if (data.status === 'not_logged_in') {
                    alert("Please log in first to add items to your cart.");
                    window.location.href = "login.php";
                } else {
                    alert(data.message || 'Error updating cart.');
                }
            })
            .catch(err => {
                console.error('Cart Error:', err);
                alert("Please log in first to add items to your cart.");
            });
        });
    });
});

function validateField(inputEl, condition, message) {
    const errorEl = inputEl.parentElement.querySelector(".error-text");
    if (!condition) {
        inputEl.style.borderColor = "#d9534f";
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.style.display = "block";
        }
        return false;
    } else {
        inputEl.style.borderColor = "#3d9a5c";
        if (errorEl) {
            errorEl.style.display = "none";
        }
        return true;
    }
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}