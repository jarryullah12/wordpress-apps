<?php
/**
 * @copyright   Copyright 2019, Modern Web Services
 * @link        https://modernwebservices.com.au/
 */
declare(strict_types=1);

namespace Rtbcc;

use function in_array;

/**
 * The main plugin controller
 *
 * @since 1.0.0
 */
class Controller
{

    const VERSION            = '1.0.4';
    const EXCHANGE_RATE_API  = 'https://blockchain.info/ticker';
    const FONT_AWESOME_URL   = 'https://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css';
    const BITCOIN_ICON       = '<i class="fa fa-btc" aria-hidden="true"></i>';
    const STYLE_FONT_AWESOME = 'font-awesome';
    const STYLE_ADMIN        = 'admin';

    // TODO make this an option (this is also not yet being used)
    const DEFAULT_CURRENCY   = BitcoinExchangeRates::USD;

    const DEFAULT_DECIMAL_PLACES = 2;

    // TODO make this an option
    const MAX_CONNECTION_ATTEMPTS = 5;

    // If we fail to connect to the API after MAX_CONNECTION_ATTEMPTS, just give up.
    private $connectionAttempts = 0;

    /**
     * @var BitcoinExchangeRates
     */
    private $exchange;

    /**
     * Hook plugin into WordPress
     *
     * @since 1.0.0
     */
    public function run()
    {
        $this->register_actions();
        if (is_admin()) {
            $this->register_admin_actions();
        } else {
            $this->register_public_actions();
        }
    }


    /**
     * Ensure the Exchange Rates are only loaded once
     *
     * @return BitcoinExchangeRates
     * @since 1.0.0
     */
    protected function getExchange(): BitcoinExchangeRates
    {
        if (null === $this->exchange) {
            $this->loadExchangeRates();
        }

        return $this->exchange;
    }


    /**
     * For some reason, the API doesn't like the WordPress user agent. Oh well.
     *
     * @param mixed[] $http_request_args
     * @return mixed[]
     * @since 1.0.0
     */
    public function modify_user_agent(array $http_request_args): array
    {
        $http_request_args['user-agent'] = 'php-requests';

        return $http_request_args;
    }


    /**
     * Loads the Exchange Rates from API
     *
     * @since 1.0.0
     */
    protected function loadExchangeRates()
    {

        // Don't keep pounding the API if it's not working.
        if ($this->connectionAttempts++ >= self::MAX_CONNECTION_ATTEMPTS) {
            trigger_error(__('Unable to connect after ' . self::MAX_CONNECTION_ATTEMPTS . ' attempts. Giving Up.'), E_USER_WARNING);

            return;
        }

        // The API doesn't appear to like WP...
        add_filter('http_request_args', [$this, 'modify_user_agent']);

        // Perform the API call
        $exchange_rates = json_decode(wp_remote_retrieve_body(wp_remote_get(self::EXCHANGE_RATE_API)), true);

        if (!is_array($exchange_rates) || !count($exchange_rates)) {
            trigger_error(__("Unable to retrieve Bitcoin exchange rates from '" . self::EXCHANGE_RATE_API . "'"), E_USER_WARNING);

            return;
        }
        $this->exchange = new BitcoinExchangeRates($exchange_rates);
    }


    /**
     * Actions used on both admin + public pages
     *
     * @since 1.0.0
     */
    protected function register_actions()
    {

    }


    /**
     * Shortcode handler
     *
     * @param string[] $atts    the ['to' => 'USD']   in [convert_bitcoin to=USD]1200[/convert_bitcoin]
     * @param string   $content the '1200'            in [convert_bitcoin to=USD]1200[/convert_bitcoin]
     * @param string   $tag     the 'convert_bitcoin' in [convert_bitcoin to=USD]1200[/convert_bitcoin]
     *
     * @return string
     * @since 1.0.0
     *
     */
    public function convert_bitcoin(array $atts, string $content, string $tag): string
    {
        $value        = (float)$content;
        $fromFiat     = $atts['from'] ?? null;
        $toFiat       = $atts['to'] ?? null;
        $prefixSymbol = !isset($atts['symbol']) || $atts['symbol'] === 'true';
        $decimals     = isset($atts['decimals']) ? (int)$atts['decimals'] : self::DEFAULT_DECIMAL_PLACES;

        if (!$fromFiat && !$toFiat) {
            trigger_error(__("Invalid syntax. You must set a Fiat currency to convert from or to: [$tag]"), E_USER_WARNING);

            return '';
        }

        if ($fromFiat && $toFiat) {
            trigger_error(__("Invalid syntax. You must set a Fiat currency to convert from or to: [$tag]"), E_USER_WARNING);

            return '';
        }

        if (!$this->getExchange()) {
            return '';
        }

        if ($fromFiat) {
            $symbol = self::BITCOIN_ICON;
            $value  = $this->getExchange()->convertToBitcoin($value, $fromFiat);
        } else {
            $symbol = $this->getExchange()->getSymbol($toFiat);
            $value  = $this->getExchange()->convertToFiat($value, $toFiat);
        }

        $defaults = [
            'beforeSymbol' => '<span class="bc-symbol">',
            'afterSymbol'  => '</span>',
            'beforeValue'  => '<span class="bc-value">',
            'afterValue'   => '</span>',
            'prefixSymbol' => $prefixSymbol,
            'symbol'       => $symbol,
            'value'        => $value,
        ];

        $data = apply_filters('format_bitcoin_convert_output', $defaults);

        $formatSymbol = $data['prefixSymbol']
            ? "{$data['beforeSymbol']}%s{$data['afterSymbol']}"
            : '';

        $formatValue = "{$data['beforeValue']}%.{$decimals}f{$data['afterValue']}";

        $args   = $prefixSymbol ? [$symbol] : [];
        $args[] = $value;

        return vsprintf("$formatSymbol$formatValue", $args);
    }


    /**
     * Admin pages only
     *
     * @since 1.0.0
     */
    protected function register_admin_actions()
    {
        add_action('admin_init', [$this, 'enqueue_admin_styles']);
        add_filter('mce_buttons', [$this, 'register_tiny_mce_buttons']);
        add_filter('mce_external_plugins', [$this, 'register_tiny_mce_javascript']);
    }


    /**
     * Add Short-code Generator button to Tiny MCE
     *
     * @param array $buttons
     * @return array
     * @since 1.0.0
     */
    public function register_tiny_mce_buttons(array $buttons): array
    {
        $buttons[] = 'bitcoin_convert';

        return $buttons;
    }


    /**
     * Add Tiny MCE JS plugin
     *
     * @param array $plugin_array
     * @return array
     * @since 1.0.0
     */
    public function register_tiny_mce_javascript(array $plugin_array): array
    {
        $plugin_array['bitcoin_convert'] = plugins_url('/assets/js/tinymce-plugin.js', __DIR__);

        return $plugin_array;
    }


    /**
     * Public pages only
     *
     * @since 1.0.0
     */
    protected function register_public_actions()
    {
        add_action('init', [$this, 'enqueue_public_styles']);
        add_shortcode('convert_bitcoin', [$this, 'convert_bitcoin']);
        add_action('wp_head', [$this, 'embed_styles']);
    }


    /**
     * Trivial CSS.
     *
     * @todo move to external style-sheet
     */
    public function embed_styles()
    {
        ?>
        <!--suppress CssUnusedSymbol -->
        <style>
            .bc-symbol .fa {
                width: auto;
            }
        </style>
        <?php
    }


    /**
     * Public pages only
     *
     * @since 1.0.0
     */
    public function register_public_styles()
    {
        wp_register_style(self::STYLE_FONT_AWESOME, self::FONT_AWESOME_URL, [], self::VERSION);
    }


    /**
     * Public pages only
     *
     * @since 1.0.0
     */
    public function enqueue_public_styles()
    {
        $this->enqueue_font_awesome_style();
    }


    /**
     * Public pages only
     *
     * @since 1.0.0
     */
    public function enqueue_admin_styles()
    {
        $this->enqueue_font_awesome_style();

        wp_enqueue_style(self::STYLE_ADMIN, plugins_url('/assets/css/admin-style.css', __DIR__), [], self::VERSION);
    }


    /**
     * Enqueues the font-awesome font from MAX CDN (if no other plugin or themes has already done so)
     *
     * @since 1.0.0
     */
    protected function enqueue_font_awesome_style()
    {
        if (!in_array(self::STYLE_FONT_AWESOME, wp_styles()->queue, true)) {
            wp_enqueue_style(self::STYLE_FONT_AWESOME, self::FONT_AWESOME_URL, [], self::VERSION);
        }
    }
}
