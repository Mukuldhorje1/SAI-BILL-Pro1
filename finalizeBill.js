/** Legacy helper — main app uses Bill Checkout tab. */
function finalizeBill() {
    if (typeof goToCheckout === 'function') {
        goToCheckout();
    } else {
        alert('Open Bill Checkout from the POS tab to complete payment.');
    }
}
