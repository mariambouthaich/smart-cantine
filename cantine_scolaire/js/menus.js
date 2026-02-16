let selectedPlats = [];
let totalAmount = 0;

function selectPlat(element, platId, type) {
    const checkbox = element.querySelector('input');
    const price = parseFloat(element.querySelector('.price').textContent.replace(/[^\d.]/g, ''));
    const name = element.querySelector('h3').textContent;
    
    if (type === 'entree' || type === 'plat') {
        // Pour entrées et plats principaux: sélection unique
        const container = element.closest('.card-body');
        const items = container.querySelectorAll('.menu-item');
        
        items.forEach(item => {
            item.classList.remove('selected');
            const input = item.querySelector('input');
            input.checked = false;
            
            // Retirer du récapitulatif
            const itemId = input.value;
            const index = selectedPlats.findIndex(p => p.id == itemId);
            if (index > -1) {
                totalAmount -= selectedPlats[index].price;
                selectedPlats.splice(index, 1);
            }
        });
        
        element.classList.add('selected');
        checkbox.checked = true;
        selectedPlats.push({ id: platId, name: name, price: price, type: type });
        totalAmount += price;
    } else {
        // Pour desserts: sélection multiple
        if (element.classList.contains('selected')) {
            element.classList.remove('selected');
            checkbox.checked = false;
            const index = selectedPlats.findIndex(p => p.id == platId);
            if (index > -1) {
                totalAmount -= selectedPlats[index].price;
                selectedPlats.splice(index, 1);
            }
        } else {
            element.classList.add('selected');
            checkbox.checked = true;
            selectedPlats.push({ id: platId, name: name, price: price, type: type });
            totalAmount += price;
        }
    }
    
    updateCart();
}

function updateCart() {
    const cartItems = document.getElementById('cartItems');
    const totalAmountEl = document.getElementById('totalAmount');
    
    if (selectedPlats.length === 0) {
        cartItems.innerHTML = '<p style="text-align: center; opacity: 0.6;">Aucun plat sélectionné</p>';
    } else {
        let html = '';
        selectedPlats.forEach(plat => {
            html += `
                <div class="cart-item">
                    <span>${plat.name}</span>
                    <span>${plat.price.toFixed(2)} DH</span>
                </div>
            `;
        });
        cartItems.innerHTML = html;
    }
    
    totalAmountEl.textContent = totalAmount.toFixed(2) + ' DH';
}

// Initialiser le récapitulatif
document.addEventListener('DOMContentLoaded', function() {
    updateCart();
});
