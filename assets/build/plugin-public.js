class YespoTracker
{
    constructor() {
        this.category = trackingData.category;
        this.product = trackingData.product;

        this.start();
    }

    start(){
        if(this.category) this.sendCategory(this.category);
        if(this.product) this.sendProduct(this.product);
    }

    sendCategory(category){
        console.log('Category key has been sent with id:', category);
        eS('sendEvent', 'CategoryPage', {
            "CategoryPage": {
                "categoryKey": category.categoryKey
            }
        });
    }

    sendProduct(product){
        console.log('Product key has been sent with id:', product);
        eS('sendEvent', 'ProductPage', {
            'ProductPage': {
                'productKey': product.id,
                'price': product.price,
                'isInStock': product.stock
            }
        });
    }

}


document.addEventListener('DOMContentLoaded', function() {
    if (typeof window.trackingData !== 'undefined' && typeof eS === 'function') {
        new YespoTracker();
    } else {
        console.log('trackingData is not defined');
    }
});
