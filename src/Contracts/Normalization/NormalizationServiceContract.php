<?php


namespace AliSyria\LDOG\Contracts\Normalization;


interface NormalizationServiceContract
{
    public static function capitalize(string $input):string ;
    public static function lowercase(string $input):string ;
    public static function uppercase(string $input):string ;
    public static function dateISO8601(string $input):string ;
}