document.addEventListener("DOMContentLoaded", function() {

    const gateForm = document.querySelector('#partner-edit__bank-gates-edit-modal__gate-form')

    if (gateForm) {

        const fixedComissionCurrency = gateForm.querySelector('select[name=FeeCurrencyId]')
        const minimalComissionCurrency = gateForm.querySelector('select[name=MinimalFeeCurrencyId]')
        const fixedCurrencyElements = gateForm.querySelectorAll('#comissionWrapper .fixedComissionCurrency')
        const minimalCurrencyElements = gateForm.querySelectorAll('#comissionWrapper .minimalComissionCurrency')

        function getSelectedCurrency (dropDown) {
            const selectedCurrency = dropDown.options[dropDown.options.selectedIndex]
            return selectedCurrency
        }

        function updateCurrency(selectedCurrency, elements) {
            try {
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
            const selectedCurrency = getSelectedCurrency(e.target)
            updateCurrency(selectedCurrency, fixedCurrencyElements)
        })

        minimalComissionCurrency.addEventListener('change', function (e) {
            const selectedCurrency = getSelectedCurrency(e.target)
            updateCurrency(selectedCurrency, minimalCurrencyElements)
        })

        const addGateBtn = document.querySelector('#partner-edit__bank-gates-table__add-button')
        if (addGateBtn) {
            addGateBtn.addEventListener('click', function () {
                // очищаем поля при добавлении нового шлюза
                updateCurrency({}, fixedCurrencyElements)
                updateCurrency({}, minimalCurrencyElements)

            })
        }

        const editGateBtns = document.querySelectorAll('.partner-edit__bank-gates-table__edit-button')
        if (editGateBtns) {
            editGateBtns.forEach(function (button) {
                button.addEventListener('click', function () {
                    setTimeout(function () {
                        const fixedCurrency = getSelectedCurrency(fixedComissionCurrency)
                        const minimalCurrency = getSelectedCurrency(minimalComissionCurrency)

                        updateCurrency(fixedCurrency, fixedCurrencyElements)
                        updateCurrency(minimalCurrency, minimalCurrencyElements)
                        // ждем обновления selectedIndex 100 миллисекунд, иначе не успевает отобразиться информация
                    }, 100)
                })
            })
        }
    }

});


