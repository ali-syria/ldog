<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\Authentication\User;
use AliSyria\LDOG\Contracts\GraphStore\ConnectionContract;
use AliSyria\LDOG\Exceptions\UserAlreadyExist;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\Tests\TestCase;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use EasyRdf\Graph;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserTest extends TestCase
{
    protected ConnectionContract $connection;

    public function setUp(): void
    {
        parent::setUp();
        $this->connection=GS::secureConnection();
        $this->connection->clearAll();
    }

    public function testCreateUser()
    {
        $user=User::create('ali','secret');
        $ldogPrefix=UriBuilder::PREFIX_LDOG;

        $resultSet=$this->connection->jsonQuery("
            PREFIX ldog: <$ldogPrefix>
            SELECT ?username ?passwordHash ?hashAlgorithm
            WHERE {
                ?user a ldog:LoginAccount;
                      ldog:username ?username;
                      ldog:password ?password.
                ?password ldog:hashValue ?passwordHash;
                          ldog:hashAlgorithm  ?hashAlgorithm .       
            }
        ");
        $users=[];
        foreach ($resultSet as $result)
        {
            $users[]=[
               'username'=> $result->username->getValue(),
               'password'=> $result->passwordHash->getValue(),
               'hashAlgorithmUri'=> $result->hashAlgorithm->getUri()
            ];
        }
        $firstUserInResult=data_get($users,0,[]);

        $this->assertInstanceOf(User::class,$user,'created user instance is User');

        $this->assertTrue(data_get($firstUserInResult,'username')==='ali','retrieved user  is correct');

        $this->assertEquals(1,$this->count($users),'only one user with the same username exist');

        $this->assertTrue(data_get($firstUserInResult,'username')===$user->username
            && data_get($firstUserInResult,'password')===$user->password,'user credentials is correctly returned from create method');

        $this->assertEquals(UriBuilder::PREFIX_LDOG."Bcrypt",data_get($firstUserInResult,'hashAlgorithmUri'),'user password is hashed using bcrypt algorithm');
    }

    public function testThrowExceptionWhenCreatingUserAlreadyExist()
    {
        $this->expectException(UserAlreadyExist::class);

        User::create('ali','secret');
        User::create('ali','secret');
    }

    public function testDeleteUser()
    {
        $username='ali';
        $password='secret';
        $user=User::create($username,$password);
        $user->delete();

        $this->assertNull(User::retrieve($username));
    }

    public function testRetrieveUser()
    {
        $username='ali';
        $password='secret';
        $createdUser=User::create($username,$password);
        $retrievedUser=User::retrieve($username);

        $this->assertEquals($createdUser,$retrievedUser);
        $this->assertNull(User::retrieve($username."ss"));
    }

    public function testGetAuthIdentifierName()
    {
        $user=User::create('ali','secret');
        $this->assertEquals('username',$user->getAuthIdentifierName());
    }

    public function testGetAuthIdentifier()
    {
        $user=User::create('ali','secret');
        $this->assertEquals('ali',$user->getAuthIdentifier());
    }

    public function testGetAuthPassword()
    {
        $user=User::create('ali','secret');
        $this->assertTrue(Hash::check('secret',$user->getAuthPassword()));
    }

    public function testGetRememberToken()
    {
        $rememberToken=Str::random(32);
        $user=User::create('ali','secret');
        $user->setRememberToken($rememberToken);

        $this->assertEquals($rememberToken,$user->getRememberToken());
    }

    public function testSetRememberToken()
    {
        $rememberToken=Str::random(32);
        $user=User::create('ali','secret');
        $user->setRememberToken($rememberToken);

        $ldogPrefix=UriBuilder::PREFIX_LDOG;
        $rememberTokenName=$user->getRememberTokenName();
        $userUri=User::getUserUri($user->username)->getResourceUri();

        $resultSet=$this->connection->jsonQuery("
            PREFIX ldog: <$ldogPrefix>
            SELECT ?rememberToken
            WHERE {
                <$userUri> ldog:$rememberTokenName ?rememberToken .       
            }
        ");

        $retrievedRememberToken=null;
        foreach ($resultSet as $result)
        {
            if($result->rememberToken)
            {
                $retrievedRememberToken=$result->rememberToken->getValue();
            }
            break;
        }
        $this->assertEquals($rememberToken,$retrievedRememberToken);
    }

    public function testGetRememberTokenName()
    {
        $user=User::create('ali','secret');

        $this->assertEquals('rememberToken',$user->getRememberTokenName());
    }
}