jQuery(function($) {
    const phoneFields = [
        { id: "#billing_phone", name: "billing_phone" },
        { id: "#billing_phone_popup", name: "billing_phone_popup" },
        { id: "#login_your_whatsapp", name: "login_your_whatsapp" },
        { id: "#register_your_whatsapp", name: "register_your_whatsapp" }
    ];

    let utilsScriptLoaded = false;

    function debounce(func, wait) {
        let timeout;
        return function() {
            const context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }

    function initializeIntlTelInput(phoneField, allowlist, defaultCountry, localizedCountries) {
        if (!allowlist.length) {
            console.log('No allowed countries specified. Skipping initialization for', phoneField.attr('id'));
            return;
        }

        if (phoneField.data('iti-initialized')) {
            return;
        }

        function geoIpLookup(callback) {
            $.getJSON("https://ipapi.co/jsonp/?callback=?", function(resp) {
                const countryCode = (resp && resp.country) ? resp.country.toLowerCase() : defaultCountry;
                callback(countryCode);
            }).fail(function() {
                callback(defaultCountry);
            });
        }

        const iti = window.intlTelInput(phoneField[0], {
            initialCountry: "auto",
            geoIpLookup: function(success, failure) {
                geoIpLookup(function(countryCode) {
                    if (allowlist.includes(countryCode)) {
                        success(countryCode);
                    } else {
                        success(defaultCountry);
                    }
                });
            },
            onlyCountries: allowlist,
            utilsScript: woocommerceITISettings.utilsScriptUrl,
            separateDialCode: true,
            nationalMode: false,
            formatOnDisplay: true
        });

        iti.promise.then(function() {
            console.log('IntlTelInput initialized for', phoneField.attr('id'));

            const currentNumber = phoneField.val();
            if (currentNumber && currentNumber.length > 0) {
                iti.setNumber(currentNumber);
                phoneField.trigger('blur.intlTelInput');

                const countryData = iti.getSelectedCountryData();
                if (countryData.iso2) {
                    iti.setCountry(countryData.iso2);
                }
            }
        }).catch(function(error) {
            console.error('Error initializing IntlTelInput:', error);
        });

        phoneField.data('iti-instance', iti);
        phoneField.data('iti-initialized', true);

        function formatNumber(phoneField, iti) {
            const countryData = iti.getSelectedCountryData();
            const countryCode = countryData && countryData.dialCode ? countryData.dialCode : '';
            let currentNumber = phoneField.val();

            currentNumber = currentNumber.replace(new RegExp(`^(${countryCode}|0+|00+|\\+${countryCode})`), '');
            currentNumber = currentNumber.replace(/\s+/g, '');
            currentNumber = currentNumber.replace(/-/g, '');
            if (countryCode) {
                currentNumber = countryCode + currentNumber;
            }

            phoneField.val(currentNumber);
        }

        const updatePhoneNumber = debounce(function() {
            const currentNumber = phoneField.val();
            if (currentNumber && currentNumber.length > 0) {
                formatNumber(phoneField, iti);
            }
        }, 300);

        phoneField.off('blur.intlTelInput');
        phoneField.off('input.intlTelInput');
        phoneField.off('countrychange.intlTelInput');

        phoneField.on('blur.intlTelInput', updatePhoneNumber);
        phoneField.on('input.intlTelInput', debounce(function() {
            console.log('Input event on', phoneField.attr('id'));
        }, 300));
        phoneField.on('countrychange.intlTelInput', function() {
            phoneField.val('');
            updatePhoneNumber();
        });

        if (phoneField.val()) {
            iti.setNumber(phoneField.val());
        }

        if (woocommerceITISettings.isArabic) {
            setTimeout(() => {
                $('.iti__country-list .iti__country').each(function() {
                    const countryCode = $(this).attr('data-country-code');
                    const arabicName = localizedCountries[countryCode];
                    if (arabicName) {
                        $(this).find('.iti__country-name').text(arabicName);
                    }
                });
            }, 500);
        }

        const placeholder = phoneField.attr('placeholder');
        if (placeholder) {
            phoneField.attr('placeholder', placeholder.replace('+', ''));
        }
    }

    function initializePhoneFields() {
        const allowlist = woocommerceITISettings.allowlist ? woocommerceITISettings.allowlist.split(',') : [];
        const defaultCountry = woocommerceITISettings.default_country || 'us';
        const localizedCountries = woocommerceITISettings.countryNames.reduce((obj, country) => {
            obj[country.iso2] = country.name;
            return obj;
        }, {});

        phoneFields.forEach(fieldConfig => {
            const phoneField = $(fieldConfig.id);
            if (phoneField.length) {
                initializeIntlTelInput(phoneField, allowlist, defaultCountry, localizedCountries);
            }
        });
    }

    function loadIntlTelInputUtils(callback) {
        if (!utilsScriptLoaded) {
            $.getScript(woocommerceITISettings.utilsScriptUrl, function() {
                utilsScriptLoaded = true;
                callback();
            }).fail(function(jqxhr, settings, exception) {
                console.error('Failed to load intlTelInputUtils:', exception);
            });
        } else {
            callback();
        }
    }

    $(document).ready(function() {
        loadIntlTelInputUtils(initializePhoneFields);
    });

    $(document.body).on('updated_checkout', function() {
        loadIntlTelInputUtils(initializePhoneFields);
    });

    $(document).on('click', 'button[type="submit"]', function() {
        setTimeout(() => {
            loadIntlTelInputUtils(initializePhoneFields);
        }, 100);
    });

    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.addedNodes.length || mutation.removedNodes.length) {
                loadIntlTelInputUtils(initializePhoneFields);
            }
        });
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true,
    });

    $('#change-country').text(awpTranslations.changeCountry);
    $('#country-dialog').attr('title', awpTranslations.selectDefaultCountry);
});
