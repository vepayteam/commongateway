// (function ($) {
//
//     "use strict";
//
//     $('#partner-edit__bank-gates-edit-modal__gate-form')
//         .find('select[name=FeeCurrencyId]')
//         .on('change', function (e) {
//             console.log(e)
//             try {
//                 const chosenCurrencyElements = $('#comissionWrapper .chosenCurrency')
//                 const currentCurrency = e.target.options[e.target.options.selectedIndex]?.innerText
//                 chosenCurrencyElements.text(currentCurrency || '')
//             } catch (e) {
//                 console.log('ошибка изменения валюты', e)
//             }
//
//         })
//
//
// }(jQuery || $));
//

const form = document.querySelector('#partner-edit__bank-gates-edit-modal__gate-form')
const fixedComissionCurrency = form.querySelector('select[name=FeeCurrencyId]')
const minimalComissionCurrency = form.querySelector('select[name=MinimalFeeCurrencyId]')

fixedComissionCurrency.addEventListener('change', function (e) {
    try {
        const chosenCurrencyElements = form.querySelectorAll('#comissionWrapper .fixedComissionCurrency')
        const currentCurrency = e.target.options[e.target.options.selectedIndex]?.innerText
        chosenCurrencyElements.forEach(function (el) {
            el.textContent = currentCurrency || ''
        } )
    } catch (e) {
        console.log('ошибка изменения валюты', e)
    }
})

minimalComissionCurrency.addEventListener('change', function (e) {
    try {
        const chosenCurrencyElements = form.querySelectorAll('#comissionWrapper .minimalComissionCurrency')
        const currentCurrency = e.target.options[e.target.options.selectedIndex]?.innerText
        chosenCurrencyElements.forEach(function (el) {
            el.textContent = currentCurrency || ''
        } )
    } catch (e) {
        console.log('ошибка изменения валюты', e)
    }
})

