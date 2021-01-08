<?php


namespace AliSyria\LDOG\Normalization;


use AliSyria\LDOG\Contracts\Normalization\NormalizationServiceContract;
use Carbon\Carbon;
use Illuminate\Support\Str;

class Norm implements NormalizationServiceContract
{

    public static function capitalize(string $input):string
    {
        return Str::title($input);
    }

    public static function lowercase(string $input):string
    {
        return Str::lower($input);
    }

    public static function uppercase(string $input):string
    {
        return Str::upper($input);
    }

    public static function dateFormate(string $input,string $format)
    {
        $result=$input;
        try {
            $result=Carbon::parse($input)->format($format);
        }
        catch (\Exception $e)
        {

        }

        return $result;
    }
}