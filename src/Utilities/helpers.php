<?php

use AliSyria\LDOG\Utilities\LocalContext;

if(!function_exists('locale')) {
    function locale():LocalContext
    {
        return LocalContext::make();
    }
}