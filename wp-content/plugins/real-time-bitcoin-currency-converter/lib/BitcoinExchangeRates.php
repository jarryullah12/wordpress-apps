<?php
/**
 * @copyright   Copyright 2019, Modern Web Services
 * @link        https://modernwebservices.com.au/
 */
declare(strict_types=1);

namespace Rtbcc;

/**
 * Stores current exchange rates
 *
 * @since 1.0.0
 */
class BitcoinExchangeRates
{

    // Currencies. Must match JSON keys returned from the API
    const USD = 'USD';
    const JPY = 'JPY';
    const CNY = 'CNY';
    const SGD = 'SGD';
    const HKD = 'HKD';
    const CAD = 'CAD';
    const NZD = 'NZD';
    const AUD = 'AUD';
    const CLP = 'CLP';
    const GBP = 'GBP';
    const DKK = 'DKK';
    const SEK = 'SEK';
    const ISK = 'ISK';
    const CHF = 'CHF';
    const BRL = 'BRL';
    const EUR = 'EUR';
    const RUB = 'RUB';
    const PLN = 'PLN';
    const THB = 'THB';
    const KRW = 'KRW';
    const TWD = 'TWD';

    /**
     * @var array Indexed by fiat currency code
     */
    private $exchange_rates;


    /**
     * @param array $exchange_rates
     */
    public function __construct(array $exchange_rates)
    {
        $this->exchange_rates = $exchange_rates;
    }


    /**
     * @param float  $fiatQuantity
     * @param string $fiatCode
     *
     * @return float
     */
    public function convertToBitcoin(float $fiatQuantity, string $fiatCode): float
    {
        $this->checkFiatCode($fiatCode);

        return $fiatQuantity / $this->getLast($fiatCode);
    }


    /**
     * @param float  $bitcoin
     * @param string $fiatCode
     *
     * @return float
     */
    public function convertToFiat(float $bitcoin, string $fiatCode): float
    {
        $this->checkFiatCode($fiatCode);

        return $bitcoin * $this->getLast($fiatCode);
    }


    /**
     * @param string $fiatCode
     *
     * @return float
     */
    public function get15m(string $fiatCode): float
    {
        $this->checkFiatCode($fiatCode);

        return $this->exchange_rates[$fiatCode]['15m'];
    }


    /**
     * @param string $fiatCode
     *
     * @return float
     */
    public function getLast(string $fiatCode): float
    {
        $this->checkFiatCode($fiatCode);

        return $this->exchange_rates[$fiatCode]['last'];
    }


    /**
     * @param string $fiatCode
     *
     * @return float
     */
    public function getBuy(string $fiatCode): float
    {
        $this->checkFiatCode($fiatCode);

        return $this->exchange_rates[$fiatCode]['buy'];
    }


    /**
     * @param string $fiatCode
     *
     * @return float
     */
    public function getSell(string $fiatCode): float
    {
        $this->checkFiatCode($fiatCode);

        return $this->exchange_rates[$fiatCode]['sell'];
    }


    /**
     * @param string $fiatCode
     *
     * @return string
     */
    public function getSymbol(string $fiatCode): string
    {
        $this->checkFiatCode($fiatCode);

        return $this->exchange_rates[$fiatCode]['symbol'];
    }


    /**
     * Ensure the fiat currency code is known
     *
     * @param string $fiatCode
     */
    protected function checkFiatCode(string $fiatCode)
    {
        if (!array_key_exists($fiatCode, $this->exchange_rates)) {
            trigger_error(__("Unknown Fiat Currency code: $fiatCode"), E_USER_WARNING);
        }
    }
}
