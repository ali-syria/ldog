<?php


namespace AliSyria\LDOG\Contracts\Normalization;


interface NormalizerContract
{
    /**
     * @param string|null $input
     * @param string $normalizationFunctionUri
     * @return string|null
     */
    public static function handle(?string $input, string $normalizationFunction):?string ;
}