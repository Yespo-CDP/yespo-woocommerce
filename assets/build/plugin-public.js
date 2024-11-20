class YespoTracker
{
    constructor(action = null) {
        this.ajaxUrl = trackingData.ajaxUrl;
        this.getCartContentNonce = trackingData.getCartContentNonce;
        this.action = action;
        this.storageProductAdded = 'productAdded';
        if (trackingData.category) this.category = trackingData.category;
        if (trackingData.product) this.product = trackingData.product;
        if (trackingData.cart) this.cart = trackingData.cart;
        if (trackingData.thankYou) this.thankYou = trackingData.thankYou;
        if (trackingData.customerData) this.customerData = trackingData.customerData;

        this.start();
    }

    start(){
        if(this.thankYou && this.action === null) this.thankYouPage(this.thankYou);
        if(this.category && this.action === null) this.sendCategory(this.category);
        if(this.product && this.action === null) this.sendProduct(this.product);
        if(this.cart && this.action === null) this.sendCart(this.cart);
        if(this.customerData && this.action === null) this.userData(this.customerData);
        //if(this.action === 'cart' || this.action === 'cart_empty') this.getCartData();
        if(this.action === 'cart') this.getCartData('cart');
        if(this.action === 'cart_empty') this.getCartData('cart_empty');
        if(this.action === 'cart_batch') this.getCartData('cart_batch');
    }

    userData(customerData){
        eS('sendEvent', 'CustomerData', { 'CustomerData': { 'externalCustomerId': String(customerData.externalCustomerId), 'user_email': String(customerData.user_email), 'user_name': String(customerData.user_name), 'user_phone': String(customerData.user_phone) } });
    }

    thankYouPage(purchase){
        const purchasedItems = this.thankYouPageMapping(purchase);
        eS('sendEvent', 'PurchasedItems', { "OrderNumber": purchase.OrderNumber, "PurchasedItems": purchasedItems, "GUID": purchase.GUID});
    }

    sendCategory(category){
        eS('sendEvent', 'CategoryPage', { "CategoryPage": { "categoryKey": category.categoryKey } });
    }

    sendProduct(product){
        eS('sendEvent', 'ProductPage', { 'ProductPage': { 'productKey': product.id, 'price': product.price, 'isInStock': parseInt(product.stock) } });
    }

    sendCart(cart){
        const statusCart = this.cartMapping(cart);
        eS('sendEvent', 'StatusCart', { 'StatusCart': statusCart, 'GUID': cart.GUID });
    }

    cartMapping(cart){
        let status = [];
        if (cart && cart.products && (this.action === 'cart' || this.action === 'cart_batch')) {
            cart.products.forEach(product => {
                status.push({
                    'productKey': String(product.productKey),
                    'price': String(product.price),
                    'quantity': String(product.quantity),
                    'currency': String(product.currency)
                });
            });
        }

        return status;
    }

    thankYouPageMapping(purchase){
        let items = [];
        if (purchase && purchase.PurchasedItems) {
            purchase.PurchasedItems.forEach(product => {
                items.push({
                    'productKey': String(product.productKey),
                    'price': String(product.price),
                    'quantity': String(product.quantity),
                    'currency': String(product.currency)
                });
            });
        }

        return items;
    }

    getCartData(cart){
        let xhr = new XMLHttpRequest();
        let url = this.ajaxUrl;

        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = () => {
            if (xhr.readyState === 4 && xhr.status === 200) {
                let response = JSON.parse(xhr.responseText);
                if (((response.data.cart && response.data.cart.products && response.data.cart.products.length > 0) && cart === 'cart') || cart === 'cart_empty' || cart === 'cart_batch') this.sendCart(response.data.cart);
            } else if (xhr.readyState === 4) {
                console.log('Error fetching cart data:', xhr.status, xhr.statusText);
            }
        };

        let data = 'action=yespo_get_cart_contents&yespo_get_cart_nonce_name=' + encodeURIComponent(this.getCartContentNonce);
        xhr.send(data);
    }

    static trackCartChanges(){
        jQuery(document.body).on('updated_cart_totals', function(){
            if (typeof window.trackingData !== 'undefined' && typeof eS === 'function') {
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

                return originalFetch.apply(this, arguments)
                    .then(response => {
                        new YespoTracker('cart_batch');

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

    static interceptXMLHttpRequest() {
        const originalOpen = XMLHttpRequest.prototype.open;
        const originalSend = XMLHttpRequest.prototype.send;
        let hasTriggered = false;
        let storaged = sessionStorage.getItem(this.storageProductAdded)

        XMLHttpRequest.prototype.open = function (method, url) {
            if ( (url.includes('wc-ajax=add_to_cart') || url.includes('wc-ajax=get_refreshed_fragments')  ) && (storaged !== 'true')  && !hasTriggered ) {

                hasTriggered = true;
                console.log('AJAX add to cart triggered');
                sessionStorage.removeItem(this.storageProductAdded);
                setTimeout(() => {
                    new YespoTracker('cart');
                    hasTriggered = false;
                }, 2000);
            }
            originalOpen.apply(this, arguments);
        };

        XMLHttpRequest.prototype.send = function () {
            originalSend.apply(this, arguments);
        };
    }

    static emptyCart(){
        document.addEventListener('click', function(event) {
            if (event.target.closest('a[href*="remove_item"]')) {
                let cartItems = document.querySelectorAll('.cart_item');
                if (cartItems.length < 2) {
                    new YespoTracker('cart_empty');
                }
            }
        });
    }


    static addProductStorage(){
        const addToCartButton = document.querySelector('.single_add_to_cart_button');

        if (addToCartButton) {
            addToCartButton.addEventListener('click', function(event) {
                sessionStorage.setItem(this.storageProductAdded, 'true');
            });
        }
    }

    static getProductStorage(){
        if (sessionStorage.getItem(this.storageProductAdded) === 'true') {
            new YespoTracker('cart');
            sessionStorage.removeItem(this.storageProductAdded);
        }
    }

    static init(){
        if (typeof window.trackingData !== 'undefined' && typeof eS === 'function') {
            new YespoTracker();

            YespoTracker.trackCartChanges();
            if(!document.body.classList.contains('woocommerce-checkout')) {
                YespoTracker.interceptXMLHttpRequest();
                YespoTracker.interceptFetch();
            }
            YespoTracker.emptyCart();
            YespoTracker.addProductStorage();
            YespoTracker.getProductStorage();

        } else {
            console.log('trackingData is not defined');
        }


    }
}

document.addEventListener('DOMContentLoaded', YespoTracker.init);