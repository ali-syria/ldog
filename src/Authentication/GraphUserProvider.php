<?php


namespace AliSyria\LDOG\Authentication;


use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Hash;

class GraphUserProvider implements UserProvider
{

    public function retrieveById($identifier)
    {
        return User::retrieve($identifier);
    }

    public function retrieveByToken($identifier, $token)
    {
        $user=User::retrieve($identifier);

        $rememberToken=$user->getRememberToken();

        return  $rememberToken && hash_equals($rememberToken,$token)
            ? $user : null;
    }

    public function updateRememberToken(Authenticatable $user, $token)
    {
        $user->setRememberToken($token);
    }

    public function retrieveByCredentials(array $credentials)
    {
        throw_if(!isset($credentials['username']),
            new \RuntimeException('username required for authentication'));

        return User::retrieve($credentials['username']);
    }

    public function validateCredentials(Authenticatable $user, array $credentials)
    {
        $plain=$credentials['password'];

        return Hash::check($plain,$user->getAuthPassword());
    }
}