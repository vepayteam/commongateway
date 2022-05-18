
if (document.querySelector('#partner-edit__bank-gates-edit-modal__gate-form')) {
    const form = document.querySelector('#partner-edit__bank-gates-edit-modal__gate-form')
    const fixedComissionCurrency = form.querySelector('select[name=FeeCurrencyId]')
    const minimalComissionCurrency = form.querySelector('select[name=MinimalFeeCurrencyId]')

    function updateCurrency(e, elements) {
        try {
            const selectedCurrency = e.target.options[e.target.options.selectedIndex]
            let currentCurrency;
            if (selectedCurrency) {
                currentCurrency = selectedCurrency.innerText
            }
            if (elements) {
                elements.forEach(function (el) {
                    el.textContent = currentCurrency || ''
                })
            }
        } catch (e) {
            console.log('ошибка изменения валюты', e)
        }


    }

    fixedComissionCurrency.addEventListener('change', function (e) {
        const chosenCurrencyElements = form.querySelectorAll('#comissionWrapper .fixedComissionCurrency')
        updateCurrency(e, chosenCurrencyElements)
    })

    minimalComissionCurrency.addEventListener('change', function (e) {
        const chosenCurrencyElements = form.querySelectorAll('#comissionWrapper .minimalComissionCurrency')
        updateCurrency(e, chosenCurrencyElements)
    })

}