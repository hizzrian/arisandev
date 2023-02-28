// create roulette
var roulette = new roulette({
    el: '#roulette',
    data: {
        items: [
            { name: 'item1', value: 1 },
            { name: 'item2', value: 2 },
            { name: 'item3', value: 3 },
            { name: 'item4', value: 4 },
            { name: 'item5', value: 5 },
            { name: 'item6', value: 6 },
            { name: 'item7', value: 7 },
            { name: 'item8', value: 8 },
            { name: 'item9', value: 9 },
            { name: 'item10', value: 10 },
            { name: 'item11', value: 11 },
            { name: 'item12', value: 12 },
        ],
        speed: 100,
        duration: 5000,
        delay: 0,
        stopImageNumber: 0,
        startCallback: function () {
            console.log('start');
        }
    }
});
