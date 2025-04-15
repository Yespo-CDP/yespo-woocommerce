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
        //if (trackingData.thankYou) this.thankYou = trackingData.thankYou;
        //if (trackingData.customerData) this.customerData = trackingData.customerData;
        if (trackingData.front) this.front = trackingData.front;
        if (trackingData.notFound) this.notFound = trackingData.notFound;
        if (trackingData.tenantWebId) this.tenantWebIdNonce = trackingData.tenantWebId;

        this.start();
    }

    start(){
        //if(this.thankYou && this.action === null) this.thankYouPage(this.thankYou);
        if(this.category && this.action === null) this.sendCategory(this.category);
        if(this.product && this.action === null) this.sendProduct(this.product);
        if(this.cart && this.action === null) this.sendCart(this.cart);
        //if(this.customerData && this.action === null) this.userData(this.customerData);
        //if(this.action === 'cart' || this.action === 'cart_empty') this.getCartData();
        //if(this.action === 'cart') this.getCartData('cart');
        //if(this.action === 'cart_empty') this.getCartData('cart_empty');
        //if(this.action === 'cart_batch') this.getCartData('cart_batch');
        if(this.front && this.action === null) this.sendFront(this.front);
        if(this.notFound && this.action === null) this.sendNotFound(this.notFound);
        //impression start
        //this.startObserver('.type-product');
        //this.checkWebIdOnLoad();
        this.actionTenantIdWebId();
    }


    sendCategory(category){
        eS('sendEvent', 'CategoryPage', { "CategoryPage": { "categoryKey": category.categoryKey } });
    }

    sendFront(front){
        eS('sendEvent', front.frontKey );
    }

    sendNotFound(notFound){
        eS('sendEvent', notFound.notFoundKey );
    }

    sendProductImpressions(elements) {
        const impressions = this.generateImpressions(elements);
        eS('sendEvent', 'ProductImpressions', { ProductImpression: impressions,});
    }

    sendProduct(product){
        eS('sendEvent', 'ProductPage', { 'ProductPage': { 'productKey': product.id, 'price': product.price, 'isInStock': parseInt(product.stock) } });
    }

    sendCart(cart){
        if (typeof cart.cartPageKey === 'string' && cart.cartPageKey === 'StatusCartPage') eS('sendEvent', cart.cartPageKey);
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

    //send wedId to backend
    checkWebIdOnLoad(webId, tenantId, orgId) {

        if (!webId || typeof webId !== "string" || webId.trim() === "") {
            console.warn("webId is empty or invalid, skipping request.");
            return;
        }

        if (!tenantId  || typeof tenantId !== "string" || tenantId .trim() === "") {
            console.warn("tenantId is empty or invalid, skipping request.");
            return;
        }

        fetch(this.ajaxUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                action: 'save_webid',
                webId: webId,
                tenantId: tenantId,
                orgId: orgId,
                yespo_tenant_webid_nonce_name: this.tenantWebIdNonce
            })
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! Status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => console.log('Answer of WordPress server:', data))
            .catch(error => console.error('Error sending webId:', error));
    }

    actionTenantIdWebId() {
        const observeConfig = { childList: true, subtree: true };
        const observer = new MutationObserver(() => {
            if (window._esConfig) {
                observer.disconnect();
                const tenantId = window._esConfig?.tenantId || "";
                const orgId = window._esConfig?.orgId || "";
                let webId = "";

                try {
                    const match = document.cookie.match(new RegExp('(?:^|; )' + 'sc' + '=([^;]*)'));
                    webId = match ? decodeURIComponent(match[1]) : null;
                } catch (error) {
                    console.error("Failed to parse document.cookie:", error);
                }

                this.checkWebIdOnLoad(webId, tenantId, orgId);
            }
        });

        observer.observe(document, observeConfig);
    }

    /*** Observer Interaction functions ***/
    //mapping
    generateImpressions(elements) {
        if (!Array.isArray(elements) || elements.length === 0) {
            return [];
        }

        return elements.map(element => {
            const productId = element || '';
            const containerType = element.container_type || '';

            return {
                product_id: productId,
                container_type: containerType,
            };
        });
    }

    callbackObserver(entries, observer) {
        let products = [];
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const postClass = Array.from(entry.target.classList).find(cls => /^post-\d+$/.test(cls));

                if (postClass) {
                    const match = postClass.match(/^post-(\d+)$/);
                    if (match) {
                        const id = match[1];
                        products.push(id);
                    }
                }
            }
        });
        if (products.length > 0) this.sendProductImpressions(products);
    }


    //create options
    optionsObserver() {
        return {
            root: null,
            rootMargin: '0px',
            threshold: 0.5,
        };
    }

    startObserver(observed) {
        const observer = new IntersectionObserver(this.callbackObserver.bind(this), this.optionsObserver());
        const elements = document.querySelectorAll(observed);
        elements.forEach(element => observer.observe(element));
    }

}

document.addEventListener('DOMContentLoaded', YespoTracker.init);