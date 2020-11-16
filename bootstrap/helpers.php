<?php

function route_class()
{
    return str_replace('.', '-', Route::currentRouteName());
}

function ngrok_url($routeName, $parameters = [])
{
    if (app()->environment('local') && $url = config('app.ngrok_url')) {
        return $url.route($routeName, $parameters, false);
    }

    return route($routeName, $parameters);
}

//默认精确度为小数点后两位
function big_number($number, $scale =2)
{
    return new \Moontoast\Math\BigNumber($number, $scale);
}
