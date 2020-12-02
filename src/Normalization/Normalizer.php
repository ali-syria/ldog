<?php


namespace AliSyria\LDOG\Normalization;


use AliSyria\LDOG\Contracts\Normalization\NormalizerContract;
use Illuminate\Support\Str;

class Normalizer implements NormalizerContract
{

    public static function handle(?string $input, string $normalizationFunctionUri): ?string
    {
        $targetMethod=self::extractTargetMethod($normalizationFunctionUri);
        return Norm::{$targetMethod}($input);
    }
    public static function extractTargetMethod(string $normalizationFunctionUri):string
    {
        return lcfirst(Str::after($normalizationFunctionUri,'#'));
    }
}