
function toggleProductVisibility(productId) {
    fetch('toggle_visibility.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ product_id: productId }),
    })
    .then((response) => response.json())
    .then((data) => {
        if (data.success) {
            alert('Product visibility updated.');
            const productCard = document.querySelector(`[data-product-id="${productId}"]`);
            if (data.is_visible) {
                productCard.style.opacity = '1'; // Fully visible for admin
            } else {
                productCard.style.opacity = '0.5'; // Faded for admin
            }
        } else {
            alert(data.message || 'Failed to update visibility.');
        }
    })
    .catch((error) => console.error('Error toggling product visibility:', error));
}
