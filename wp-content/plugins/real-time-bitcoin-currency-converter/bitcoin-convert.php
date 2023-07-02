<?php
/**
 * @author      Andrew Ryan
 * @copyright   2016 Modern Web Services
 * @license     GPL-2.0+
 *
 * @wordpress-plugin
 * Plugin Name: Real-time Bitcoin Converter
 * Plugin URI:  https://github.com/andrewryantech/bitcoin-convert-wp-plugin
 * Description: Convert any quantity of any currency to BitCoin in real-time.
 * Version: 1.0.7
 * Author:      Andrew Ryan
 * Author URI:  https://github.com/andrewryantech
 * License:     GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 *
 * General Architecture:
 *
 * Code is divided into 2 sections, admin + public.
 *
 * When the system encounters a quantity to convert to or from Bitcoin, it performs a CURL call using the
 * WordPress standard HTTP API to get all latest bitcoin exchange rates. It then stores these values in
 * a singleton. Subsequent conversions then use these values in the singleton to avoid repeated CURL calls.
 *
 * Even though we may just need, eg USD -> BC rate, we get ALL rates as the cost of two calls, each for one currency, is far
 * more than the overhead of a single call for all currencies.
 */
declare(strict_types=1);

use Rtbcc\Plugin;

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

require_once __DIR__ . '/vendor/autoload.php';

(new Plugin())->run();


