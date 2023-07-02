/**
 * Author: https://github.com/andrewryantech
 * Created: 31/12/16 9:55 PM
 */


(function() {
    tinymce.create('tinymce.plugins.bitcoin_convert', {
        /**
         * Initializes the plugin, this will be executed after the plugin has been created.
         * This call is done before the editor instance has finished it's initialization so use the onInit event
         * of the editor instance to intercept that event.
         *
         * @param {tinymce.Editor} editor Editor instance that the plugin is initialized in.
         * @param {string} url Absolute URL to where the plugin is located.
         */
        init : function(editor, url) {

            editor.addButton('bitcoin_convert', {
                title : 'Add Bitcoin Conversion shortcode',

                icon  : 'bitcoin fa fa-btc',
                onclick: function() {
                    // Open window
                    editor.windowManager.open({
                        title: 'Bitcoin Converter Shortcode Generator',
                        body: [
                            {type: 'radio',   name: 'conversion', label: 'From Fiat to Bitcoin', checked: true},
                            {type: 'radio',   name: 'symbol',     label: 'Prefix Symbol',        checked: true},
                            {type: 'textbox', name: 'value',      label: 'Value',                placeholder: '0.00'},
                            {type: 'listbox', name: 'fiat',       label: 'Fiat Currency',        values: [
                                {text: 'AUD',   value: 'AUD', selected: 'selected'},
                                {text: 'USD',   value: 'USD'},
                                {text: 'JPY',   value: 'JPY'},
                                {text: 'CNY',   value: 'CNY'},
                                {text: 'SGD',   value: 'SGD'},
                                {text: 'HKD',   value: 'HKD'},
                                {text: 'CAD',   value: 'CAD'},
                                {text: 'NZD',   value: 'NZD'},
                                {text: 'CLP',   value: 'CLP'},
                                {text: 'GBP',   value: 'GBP'},
                                {text: 'DKK',   value: 'DKK'},
                                {text: 'SEK',   value: 'SEK'},
                                {text: 'ISK',   value: 'ISK'},
                                {text: 'CHF',   value: 'CHF'},
                                {text: 'BRL',   value: 'BRL'},
                                {text: 'EUR',   value: 'EUR'},
                                {text: 'RUB',   value: 'RUB'},
                                {text: 'PLN',   value: 'PLN'},
                                {text: 'THB',   value: 'THB'},
                                {text: 'KRW',   value: 'KRW'},
                                {text: 'TWD',   value: 'TWD'}
                            ]},
                            {type: 'textbox', name: 'decimals',   label: 'Decimal places', placeholder: '2'},
                        ],
                        onsubmit: function (e) {
                            // Insert content when the window form is submitted
                            var conversion = (e.data.conversion ? 'from' : 'to') + '=' + e.data.fiat;
                            var symbol     = ' symbol=' + (e.data.symbol ? 'true' : 'false');
                            var decimals   = ' decimals=' + e.data.decimals;
                            var value      = e.data.value;

                            var shortcode = '[convert_bitcoin ' + conversion + symbol + decimals + ']' + value + '[/convert_bitcoin]';
                            editor.insertContent(shortcode);
                        }
                    });
                }
            });

        },


        /**
         * Returns information about the plugin as a name/value array.
         * The current keys are longname, author, authorurl, infourl and version.
         *
         * @return {Object} Name/value array containing information about the plugin.
         */
        getInfo : function() {
            return {
                longname:  'Real-time Bitcoin Conversions',
                author:    'Andrew Ryan',
                authorurl: 'https://github.com/andrewryantech',
                infourl:   'https://github.com/andrewryantech/bitcoin-convert-wp-plugin',
                version:   "1.0.0"
            };
        }
    });

    // Register plugin
    tinymce.PluginManager.add( 'bitcoin_convert', tinymce.plugins.bitcoin_convert );
})();