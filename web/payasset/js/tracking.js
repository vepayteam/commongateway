(function ($) {

    "use strict";

    let tracking = {

        ajax: function (hash) {
            $.ajax({
                url: '/antifraud/default/register-tracking',
                method: 'POST',
                data: 'hash=' + hash + '&transaction_id=' + this.transactionId(),
            });
        },

        sendToServer: function () {

            (async () => {
                    const components = await Fingerprint2.getPromise();
                    const values = components.map(component => component.value);
                    const murmur = Fingerprint2.x64hash128(values.join(""), 31);
                    tracking.ajax(murmur);
                    //здесь писать
                    $('.user_hash').val(murmur);
                }
            )();

            /*Fingerprint2.get({
                preprocessor: function(key, value) {
                    if (key == "userAgent") {
                        var parser = new UAParser(value);
                        var userAgentMinusVersion = parser.getOS().name + ' ' + parser.getBrowser().name
                        return userAgentMinusVersion
                    }
                    return value
                },
                excludes: {
                    adBlock: true //исключили адблок из расчета хэша.
                }
            }, function (components) {
                var murmur = Fingerprint2.x64hash128(components.map(function (pair) {
                    return pair.value
                }).join(), 31);
                tracking.ajax(murmur);
                //здесь писать
                $('.user_hash').val(murmur);
            });*/
        },

        transactionId: function () {
            return $('.idPay').val();
        }
    };
    window.tracking = tracking;

}(jQuery || $));
