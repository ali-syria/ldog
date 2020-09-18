<?php


namespace AliSyria\LDOG\Contracts\Authentication;


use Illuminate\Contracts\Auth\Authenticatable;

interface AccountManagement
{
    public static function retrieve(string $username):?self ;
    public static function create(string $username,string $password):self;
    public function delete():void;
}