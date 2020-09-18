<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\Authentication\GraphUserProvider;
use AliSyria\LDOG\Authentication\User;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\Tests\TestCase;
use Illuminate\Support\Str;

class GraphUserProviderTest extends TestCase
{
    protected GraphUserProvider $userProvider;

    public function setUp(): void
    {
        parent::setUp();
        GS::getConnection()->clearAll();

        $this->userProvider=new GraphUserProvider();
    }

    public function testRetrieveById()
    {
        $user=User::create('ali','secret');
        $authentictable=$this->userProvider->retrieveById($user->username);

        $this->assertEquals($user,$authentictable);
    }

    public function testRetrieveByToken()
    {
        $rememberToken=Str::random(32);
        $user=User::create('ali','secret');
        $user->setRememberToken($rememberToken);

        $authentictable=$this->userProvider->retrieveByToken($user->username,$rememberToken);

        $this->assertEquals($authentictable,$user);
    }

    public function testUpdateRememberToken()
    {
        $rememberToken=Str::random(32);
        $user=User::create('ali','secret');
        $this->userProvider->updateRememberToken($user,$rememberToken);

        $this->assertEquals($rememberToken,$user->getRememberToken());
        $this->assertEquals($rememberToken,User::retrieve($user->username)->getRememberToken());
    }

    public function testRetrieveByCredentials()
    {
        $user=User::create('ali','secret');

        $authentictable=$this->userProvider->retrieveByCredentials([
            'username'=>$user->username
        ]);

        $this->assertEquals($authentictable,$user);
    }

    public function testRetrieveByCredentialsWithoutUsernameKey()
    {
        $this->expectException(\RuntimeException::class);

        $user=User::create('ali','secret');

        $authentictable=$this->userProvider->retrieveByCredentials([
            'id'=>$user->username
        ]);
    }

    public function testValidateCredentials()
    {
        $password='secret';
        $user=User::create('ali',$password);

        $this->assertTrue($this->userProvider->validateCredentials($user,[
            'password'=>$password
        ]));
        $this->assertFalse($this->userProvider->validateCredentials($user,[
            'password'=>'invalid'
        ]));
    }
}