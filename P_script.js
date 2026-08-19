document.addEventListener('DOMContentLoaded', () => {

    
    // quantity increase and decrease
    
    document.querySelectorAll('.quantity-control').forEach(control => {
        const minusBtn = control.querySelector('.btn-minus');
        const plusBtn = control.querySelector('.btn-plus');
        const qtyVal = control.querySelector('.qty-val');

        if (minusBtn && qtyVal) {
            minusBtn.addEventListener('click', () => {
                let current = parseInt(qtyVal.textContent, 10) || 1;
                if (current > 1) {
                    qtyVal.textContent = current - 1;
                }
            });
        }

        if (plusBtn && qtyVal) {
            plusBtn.addEventListener('click', () => {
                let current = parseInt(qtyVal.textContent, 10) || 1;
                qtyVal.textContent = current + 1;
            });
        }
    });



    // add to cart
    document.querySelectorAll('.btn-add-cart').forEach(button => {
        button.addEventListener('click', (e) => {
            const card = e.target.closest('.product-card');

            if (!card) return;

            // extract data from card
            const productId = card.getAttribute('data-id') || 0;
            const productName = card.querySelector('.product-name')?.textContent.trim() || '';
            const qtyElement = card.querySelector('.qty-val');
            const quantity = qtyElement ? (parseInt(qtyElement.textContent, 10) || 1) : 1;

            // fixed class selector 
            const rawPrice = card.querySelector('.price')?.textContent || '0';
            const productPrice = parseFloat(rawPrice.replace(/[^0-9.]/g, '')) || 0;

            const imgElement = card.querySelector('.product-image');
            const productImage = imgElement ? imgElement.getAttribute('src') : '';

            // send payload to php handler
            const formData = new URLSearchParams();
            formData.append('product_id', productId);
            formData.append('product_name', productName);
            formData.append('product_price', productPrice);
            formData.append('quantity', quantity);
            formData.append('product_image', productImage);

            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded'
                },
                body: formData.toString()
            })
            .then(async response => {
                const text = await response.text();
                try {
                    return JSON.parse(text);
                } catch (err) {
                    console.error('Server returned invalid JSON response:', text);
                    throw new Error('Non-JSON server response');
                }
            })
            .then(data => {
                if (data.status === 'success') {
                    showCartMessage(productName, quantity);
                } else {
                    alert(data.message || 'Error updating cart.');
                }
            })
            .catch(err => {
                console.error('Cart Error:', err);
                alert('Failed to connect to the server. Make sure you are logged in or check DevTools console (F12).');
            });
        });
    });


    // success message

    function showCartMessage(productName, quantity) {
        const oldMessage = document.querySelector('.cart-success-message');
        if (oldMessage) {
            oldMessage.remove();
        }

        const message = document.createElement('div');
        message.className = 'cart-success-message';

        message.innerHTML = `
            <div class="cart-message-content">
                <p>
                    ✓ Successfully added 
                    <strong>${quantity} x ${productName}</strong> 
                    to your cart!
                </p>
                <div class="cart-message-buttons">
                    <button class="continue-shopping-btn">Continue Shopping</button>
                    <button class="view-cart-btn">View Cart</button>
                </div>
            </div>
        `;

        document.body.appendChild(message);
        
        message.querySelector('.view-cart-btn').addEventListener('click', () => {
            window.location.href = 'customer.php?tab=cart';
        });

        message.querySelector('.continue-shopping-btn').addEventListener('click', () => {
            message.remove();
        });
    }
});