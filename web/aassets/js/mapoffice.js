ymaps.ready(function () {
    var myMap = new ymaps.Map('Kontankt', {
        center: [58.591383, 49.667933],
        zoom: 16,
        controls: []
    });

    var myPlacemark = new ymaps.Placemark(myMap.getCenter(), {
        balloonContentBody: [
            '<address>',
            '<strong>Офис Teleport</strong>',
            '<br/>',
            'Адрес: 610027, Россия, г. Киров, ул. Карла Маркса 127, оф. 302',
            '<br/>',
            'Подробнее: <a href="//www.teleport.run" target="_blank">http://www.teleport.run/<a>',
            '</address>'
        ].join('')
    }, {
        preset: 'islands#redDotIcon'
    });

    myMap.geoObjects.add(myPlacemark);
});
