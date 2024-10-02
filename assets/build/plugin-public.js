class YespoTracker
{
    constructor(action = null) {
        this.ajaxUrl = trackingData.ajaxUrl;
        this.action = action;
        if (trackingData.category) this.category = trackingData.category;
        if (trackingData.product) this.product = trackingData.product;

        this.start();
    }

    start(){
        if(this.category && this.action === null) this.sendCategory(this.category);
        if(this.product && this.action === null) this.sendProduct(this.product);
        if(this.action === 'cart') this.getCartData();
    }

    sendCategory(category){
        console.log('Category key has been sent with id:', category);
        /*
        eS('sendEvent', 'CategoryPage', {
            "CategoryPage": {
                "categoryKey": category.categoryKey
            }
        });
        */
    }

    sendProduct(product){
        console.log('Product key has been sent with id:', product);
        /*
        eS('sendEvent', 'ProductPage', {
            'ProductPage': {
                'productKey': product.id,
                'price': product.price,
                'isInStock': product.stock
            }
        });
        */
    }

    sendCart(cart){
        const statusCart = this.cartMapping(cart);
        console.log(statusCart);
        /*
        eS('sendEvent', 'StatusCart', {
            'StatusCart': statusCart,
            'GUID': cart.GUID
        });
        */
    }

    cartMapping(cart){
        let status = [];
        cart.products.forEach(product => {
            status.push({
                'productKey': product.productKey,
                'price': product.price,
                'quantity': product.quantity,
                'currency': product.currency
            });
        });

        return status;
    }

    getCartData(){

        let xhr = new XMLHttpRequest();
        let url = this.ajaxUrl;

        xhr.open('POST', url, true);
        xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

        xhr.onreadystatechange = () => {
            if (xhr.readyState === 4) {
                if (xhr.status === 200) {
                    let response = JSON.parse(xhr.responseText);
                    this.sendCart(response.data.cart);
                } else {
                    console.log('Error fetching cart data:', xhr.status, xhr.statusText);
                }
            }
        };

        let data = 'action=yespo_get_cart_contents';

        xhr.send(data);
    }

}

document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.trackingData !== 'undefined' && typeof eS === 'function') {
        new YespoTracker();
    } else {
        console.log('trackingData is not defined');
    }
});



jQuery(document.body).on('updated_cart_totals', function(){
    // Код отправки собития StatusCart
    if (typeof window.trackingData !== 'undefined' && typeof eS === 'function') {
        console.log('cart changedddd');

        new YespoTracker('cart');
    }

});



/*
jQuery(document.body).on('updated_cart', function(){
    console.log('Cart has been updated_cart');
    // Дії після зміни в кошику
});

jQuery(document.body).on('wc_fragments_refreshed', function(){
    console.log('Fragments refreshed - wc_fragments_refreshed');
    // Дії після оновлення кошика
});

jQuery(document.body).on('added_to_cart', function(){
    console.log('Product added to cart');
    // Дії після додавання товару до кошика
});
*/

/* WORKS when add products in category or shop page*/
/*
const originalFetch = window.fetch;
let hasTriggered = false;

window.fetch = function () {
    if (arguments[0].includes('/wc/store/v1/batch') && !hasTriggered) {
        hasTriggered = true;
        console.log('product added to cart');

        return originalFetch.apply(this, arguments)
            .then(response => {
                new YespoTracker('cart');

                setTimeout(() => {
                    hasTriggered = false;
                }, 5000);

                return response;
            });

    } else {
        return originalFetch.apply(this, arguments);
    }
};




document.addEventListener('DOMContentLoaded', function() {
    let addToCartButton = document.querySelector('.single_add_to_cart_button');

    if (addToCartButton) {
        addToCartButton.addEventListener('click', function(event) {
            console.log('Product added to cart');
            new YespoTracker('cart');

            setTimeout(() => {
                hasTriggered = false;
            }, 10000);
        });
    }
});
*/