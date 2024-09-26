

class YespoTracker{
    constructor() {
        this.categoryKey = trackingData.categoryKey;

        this.start();
    }
    start(){
        if(this.categoryKey) this.sendCategory(this.categoryKey);
    }
    sendCategory(categoryKey){
        console.log('Category key has been sent with id:', categoryKey);
        eS('sendEvent', 'CategoryPage', {
            "CategoryPage": {
                "categoryKey": categoryKey
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
