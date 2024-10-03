
class YespoTracker
{
    constructor(action = null) {
        this.ajaxUrl = trackingData.ajaxUrl;
        this.action = action;
        if (trackingData.category) this.category = trackingData.category;
        if (trackingData.product) this.product = trackingData.product;
        if (trackingData.cart) this.cart = trackingData.cart;

        console.log(trackingData);

        this.start();
    }

    start(){
        if(this.category && this.action === null) this.sendCategory(this.category);
        if(this.product && this.action === null) this.sendProduct(this.product);
        if(this.cart && this.action === null) this.sendCart(this.cart);
        if(this.action === 'cart' || this.action === 'cart_empty') this.getCartData();
    }

    sendCategory(category){
        console.log('Category key has been sent with id:', category);
        // eS('sendEvent', 'CategoryPage', { "CategoryPage": { "categoryKey": category.categoryKey } });
    }

    sendProduct(product){
        console.log('Product key has been sent with id:', product);
        // eS('sendEvent', 'ProductPage', { 'ProductPage': { 'productKey': product.id, 'price': product.price, 'isInStock': product.stock } });
    }

    sendCart(cart){
        const statusCart = this.cartMapping(cart);
        console.log('sendCart inside', statusCart);
        console.log(cart.GUID);
        // eS('sendEvent', 'StatusCart', { 'StatusCart': statusCart, 'GUID': cart.GUID });
    }

    cartMapping(cart){
        let status = [];
        if (cart && cart.products && this.action === 'cart') {
            cart.products.forEach(product => {
                status.push({
                    'productKey': product.productKey,
                    'price': product.price,
                    'quantity': product.quantity,
                    'currency': product.currency
                });
            });
        } else {
            status.push({
                'productKey': null,
                'price': null,
                'quantity': null,
                'currency': null
            });
        }

        return status;
    }

    getCartData(){
        let xhr = new XMLHttpRequest();
        let url = this.ajaxUrl;

        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = () => {
            if (xhr.readyState === 4 && xhr.status === 200) {
                let response = JSON.parse(xhr.responseText);
                this.sendCart(response.data.cart);
            } else if (xhr.readyState === 4) {
                console.log('Error fetching cart data:', xhr.status, xhr.statusText);
            }
        };

        let data = 'action=yespo_get_cart_contents';
        xhr.send(data);
    }

    static trackCartChanges(){
        jQuery(document.body).on('updated_cart_totals', function(){
            if (typeof window.trackingData !== 'undefined' && typeof eS === 'function') {
                console.log('Cart changed');
                new YespoTracker('cart');
            }
        });
    }

    static interceptFetch(){
        const originalFetch = window.fetch;
        let hasTriggered = false;

        window.fetch = function () {
            if (arguments[0].includes('/wc/store/v1/batch') && !hasTriggered) {
                hasTriggered = true;
                console.log('Product added to cart');

                return originalFetch.apply(this, arguments)
                    .then(response => {
                        new YespoTracker('cart');

                        setTimeout(() => {
                            hasTriggered = false;
                        }, 3000);

                        return response;
                    });
            } else {
                return originalFetch.apply(this, arguments);
            }
        };
    }

    static trackAddToCartOnProductPage(){
        document.addEventListener('DOMContentLoaded', function() {
            let addToCartButton = document.querySelector('.single_add_to_cart_button');

            if (addToCartButton) {
                addToCartButton.addEventListener('click', function() {
                    console.log('Product added to cart');
                    new YespoTracker('cart');
                    /*
                                        setTimeout(() => {
                                            hasTriggered = false;
                                        }, 10000);

                     */
                });
            }
        });
    }

    static emptyCart(){
        document.addEventListener('click', function(event) {
            if (event.target.closest('a[href*="remove_item"]')) {
                let cartItems = document.querySelectorAll('.cart_item');
                if (cartItems.length < 2) {
                    console.log('Кошик порожній');
                    new YespoTracker('cart_empty');
                }
            }
        });
    }

    static addProductStorage(){
        document.addEventListener('DOMContentLoaded', function() {
            const addToCartButton = document.querySelector('.single_add_to_cart_button');
            console.log('Товар додано в сесію');

            if (addToCartButton) {
                addToCartButton.addEventListener('click', function(event) {
                    sessionStorage.setItem('productAdded', 'true');
                });
            }
        });
    }

    static getProductStorage(){
        document.addEventListener('DOMContentLoaded', function() {
            if (sessionStorage.getItem('productAdded') === 'true') {
                console.log('Товар додано в кошик зі сторінки продукту');
                new YespoTracker('cart');
                sessionStorage.removeItem('productAdded');
            }
        });
    }

    static init(){
        if (typeof window.trackingData !== 'undefined' && typeof eS === 'function') {
            new YespoTracker();
        } else {
            console.log('trackingData is not defined');
        }

        YespoTracker.trackCartChanges();
        YespoTracker.interceptFetch();
        YespoTracker.trackAddToCartOnProductPage();
        YespoTracker.emptyCart();
        //YespoTracker.addProductStorage();
        //YespoTracker.getProductStorage()
    }
}

document.addEventListener('DOMContentLoaded', YespoTracker.init);




document.addEventListener('DOMContentLoaded', function() {
    const addToCartButton = document.querySelector('.single_add_to_cart_button');

    if (addToCartButton) {
        addToCartButton.addEventListener('click', function(event) {
            sessionStorage.setItem('productAdded', 'true');
        });
    }

    if (sessionStorage.getItem('productAdded') === 'true') {
        console.log('Товар додано в кошик зі сторінки продукту');
        new YespoTracker('cart');

        sessionStorage.removeItem('productAdded');
    }
});


