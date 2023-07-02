<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitab9ce6e2a21b3af89e1ba2276ad5b3bd
{
    public static $prefixLengthsPsr4 = array (
        'R' => 
        array (
            'Rtbcc\\' => 6,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Rtbcc\\' => 
        array (
            0 => __DIR__ . '/../../..' . '/real-time-bitcoin-currency-converter/lib',
        ),
    );

    public static $classMap = array (
        'Rtbcc\\BitcoinExchangeRates' => __DIR__ . '/../../..' . '/real-time-bitcoin-currency-converter/lib/BitcoinExchangeRates.php',
        'Rtbcc\\Plugin' => __DIR__ . '/../../..' . '/real-time-bitcoin-currency-converter/lib/Plugin.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitab9ce6e2a21b3af89e1ba2276ad5b3bd::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitab9ce6e2a21b3af89e1ba2276ad5b3bd::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitab9ce6e2a21b3af89e1ba2276ad5b3bd::$classMap;

        }, null, ClassLoader::class);
    }
}