<?php

$config = new PrestaShop\CodingStandards\CsFixer\Config();

$config
    ->setUsingCache(true)
    ->setCacheFile(__DIR__.'/.php-cs-fixer.cache')
    ->getFinder()
    ->in(__DIR__.'/../../')
    ->exclude(['vendor','translations','tests']);

return $config;
