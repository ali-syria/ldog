<?php


namespace AliSyria\LDOG\Authentication;


use AliSyria\LDOG\Contracts\Authentication\AccountManagement;
use AliSyria\LDOG\Contracts\GraphStore\ConnectionContract;
use AliSyria\LDOG\Exceptions\UserAlreadyExist;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\Facades\URI;
use AliSyria\LDOG\UriBuilder\Builder;
use AliSyria\LDOG\UriBuilder\RealResourceUri;
use EasyRdf\Graph;
use Illuminate\Contracts\Auth\Authenticatable;
use phpDocumentor\Reflection\Types\Self_;

class User implements Authenticatable,AccountManagement
{
    public string $username;
    public string $password;
    public ?string $rememberToken=null;

    protected $rememberTokenName = 'rememberToken';
    protected $authIdentifierName = 'username';

    public function __construct(string $username,string $password,$rememberToken=null)
    {
        $this->username=$username;
        $this->password=$password;
        $this->rememberToken=$rememberToken;
    }

    public function getAuthIdentifierName()
    {
        return $this->authIdentifierName;
    }

    public function getAuthIdentifier()
    {
        return $this->username;
    }

    public function getAuthPassword()
    {
        return $this->password;
    }

    public function getRememberToken()
    {
        return $this->rememberToken;
    }

    public function setRememberToken($value)
    {
        $userUri=self::getUserUri($this->username)->getResourceUri();

        $ldogPrefix=Builder::PREFIX_LDOG;

        GS::secureConnection()->rawUpdate("
            PREFIX ldog: <$ldogPrefix>
            
            DELETE
            {
                <$userUri> ldog:rememberToken ?rememberToken .
            }
            
            INSERT
            { 
               <$userUri> ldog:rememberToken '$value' .     
            }    
            
            WHERE
            {
                OPTIONAL {
                    <$userUri> ldog:rememberToken ?rememberToken .
                } 
            }        
        ");
        $this->rememberToken=$value;
    }

    public function getRememberTokenName()
    {
        return $this->rememberTokenName;
    }

    public static function retrieve(string $username): ?self
    {
        $ldogPrefix=Builder::PREFIX_LDOG;

        $resultSet=GS::secureConnection()->jsonQuery("
            PREFIX ldog: <$ldogPrefix>
            
            SELECT  ?passwordHash ?hashAlgorithm ?rememberToken
            WHERE {
                ?user a ldog:LoginAccount;
                      ldog:username '$username';
                      ldog:password ?password .
                OPTIONAL {
                   ?user ldog:rememberToken ?rememberToken .
                }
                ?password ldog:hashValue ?passwordHash;
                          ldog:hashAlgorithm  ?hashAlgorithm .       
            }
        ");
        $user=null;
        foreach ($resultSet as $result)
        {
           if(optional($result)->passwordHash)
           {
               $rememberToken=optional(optional($result)->rememberToken)->getValue();
               $user=new self($username,$result->passwordHash->getValue(),$rememberToken);
           }
           break;
        }

        return $user;
    }

    public static function create(string $username,string $password): self
    {
        if(self::retrieve($username))
        {
            throw new UserAlreadyExist('User with same username already exists');
        }

        $hashedPassword=bcrypt($password);

        $userUri=self::getUserUri($username)->getResourceUri();
        $passwordUri=self::getPasswordUri($hashedPassword)->getResourceUri();

        $ldogPrefix=Builder::PREFIX_LDOG;

        GS::secureConnection()->rawUpdate("
            PREFIX ldog: <$ldogPrefix>
            
            INSERT DATA
            { 
               <$userUri> a  ldog:LoginAccount;
                        ldog:username  '$username';
                        ldog:password  <$passwordUri> .        
               <$passwordUri> a ldog:Password;
                              ldog:hashAlgorithm ldog:Bcrypt;
                              ldog:hashValue '$hashedPassword' .     
            }            
        ");
        return new self($username,$hashedPassword);
    }

    public function delete(): void
    {
        $userUri=self::getUserUri($this->username)->getResourceUri();
        $passwordUri=self::getPasswordUri($this->password)->getResourceUri();

        $ldogPrefix=Builder::PREFIX_LDOG;

        GS::secureConnection()->rawUpdate("
            PREFIX ldog: <$ldogPrefix>
            
            DELETE
            { 
               <$userUri> ?userRelation  ?userObject.
               <$passwordUri> ?passwordRelation  ?passwordObject.  
            }
            WHERE
            {
               <$userUri> ?userRelation  ?userObject.
               <$passwordUri> ?passwordRelation  ?passwordObject.              
            }            
        ");
    }

    public static function getUserUri(string $username):RealResourceUri
    {
        return URI::realResource('accounts','LoginAccount',$username);
    }
    public static function getPasswordUri(string $hashedPassword):RealResourceUri
    {
        return URI::realResource('accounts','Password',$hashedPassword);
    }
}