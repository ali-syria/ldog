<?php


namespace AliSyria\LDOG\Tests\Feature;


use AliSyria\LDOG\Authentication\User;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class AuthenticationTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        $this->connection=GS::secureConnection();
        $this->connection->clearAll();
    }
    
}