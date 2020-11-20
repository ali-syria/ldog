<?php


namespace AliSyria\LDOG\OrganizationManager;


use AliSyria\LDOG\Authentication\User;
use AliSyria\LDOG\Contracts\Authentication\AccountManagement;
use AliSyria\LDOG\Contracts\OrganizationManager\EmployeeContract;
use AliSyria\LDOG\Contracts\OrganizationManager\OrganizationContract;
use AliSyria\LDOG\Contracts\UriBuilder\RealResourceUriContract;
use AliSyria\LDOG\Exceptions\EmployeeAlreadyExist;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\Facades\URI;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use Illuminate\Support\Str;

class Employee implements EmployeeContract
{
    protected string $id;
    protected string $uri;
    protected string $name;
    protected string $description;
    protected OrganizationContract $organization;
    protected AccountManagement $loginAccount;

    public const LDOG_CLASS='Employee';

    public function __construct(OrganizationContract $organization,AccountManagement $loginAccount,
                                string $id,string $uri,string $name,string $description)
    {
        $this->id=$id;
        $this->uri=$uri;
        $this->name=$name;
        $this->description=$description;
        $this->organization=$organization;
        $this->loginAccount=$loginAccount;
    }

    public function getId():string
    {
        return $this->id;
    }
    public function getUri():string
    {
        return $this->uri;
    }
    public function getName(): string
    {
        return $this->name;
    }
    public function getDescription(): string
    {
        return $this->description;
    }

    public function getOrganization(): OrganizationContract
    {
        return $this->organization;
    }

    public function getLoginAccount(): AccountManagement
    {
        return $this->loginAccount;
    }

    public static function retrieveByLoginAccount(AccountManagement $loginAccount):?self
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;
        $loginAccountUri=$loginAccount->getUri();

        $resultSet=GS::secureConnection()->jsonQuery("
            PREFIX ldog: <$ldogPrefix>
            
            SELECT ?employee ?organization ?id ?name ?description
            WHERE {
                ?employee a ldog:Employee ;
                      ldog:hasLoginAccount <$loginAccountUri>;
                      ldog:isEmployeeOf ?organization ;
                      ldog:id ?id ;
                      ldog:name ?name ;
                      ldog:description ?description .    
            }
        ");
        $employee=null;
        foreach ($resultSet as $result)
        {
            if(optional($result)->employee)
            {
                $organizationUri=$result->organization->getUri();
                $organization=OrganizationFactory::retrieveByUri($organizationUri);
                $employee= new static($organization,$loginAccount,$result->id->getValue(),
                    $result->employee->getUri(),$result->name->getValue(),
                    $result->description->getValue());
            }
            break;
        }

        return $employee;
    }
    public static function retrieveByUri(string $uri):?self
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;


        $resultSet=GS::secureConnection()->jsonQuery("
            PREFIX ldog: <$ldogPrefix>
            
            SELECT  ?loginAccountUri ?username ?organization ?id ?name ?description
            WHERE {
                <$uri> a ldog:Employee ;
                      ldog:hasLoginAccount ?loginAccountUri ;
                      ldog:isEmployeeOf ?organization ;
                      ldog:id ?id ;
                      ldog:name ?name ;
                      ldog:description ?description .
                ?loginAccountUri ldog:username  ?username .                        
            }
        ");
        $employee=null;
        foreach ($resultSet as $result)
        {
            if(optional($result)->loginAccountUri)
            {
                $organizationUri=$result->organization->getUri();
                $organization=OrganizationFactory::retrieveByUri($organizationUri);
                $employee= new static($organization,User::retrieve($result->username->getValue()),$result->id->getValue(),
                    $uri,$result->name->getValue(),
                    $result->description->getValue());
            }
            break;
        }

        return $employee;
    }
    public static function create(OrganizationContract $organization,AccountManagement $loginAccount,
                                  string $id,string $name, string $description): EmployeeContract
    {

        $ldogClass=static::LDOG_CLASS;
        $ldogPrefix=UriBuilder::PREFIX_LDOG;

        $employeeUri=static::generateUri($id);
        $employeesGraph=$employeeUri->getSectorUri();
        $employeeUriString=$employeeUri->getResourceUri();

        throw_if(static::checkIfEmployeeExist($employeeUriString),
            new EmployeeAlreadyExist('Organization Alraedy Exists'));

        $organizationUri=$organization->getUri();
        $loginAccountUri=$loginAccount->getUri();

        $query="
            PREFIX ldog: <$ldogPrefix>
            
            INSERT DATA 
            {
                GRAPH <$employeesGraph> {
                    <$employeeUriString> a ldog:$ldogClass ;
                                       ldog:id '$id' ;
                                       ldog:name '$name' ;
                                       ldog:description '$description' ;
                                       ldog:isEmployeeOf <$organizationUri> ;
                                       ldog:hasLoginAccount <$loginAccountUri> .                                    
                }
            }  
        ";
        GS::getConnection()->rawUpdate($query);

        return new static($organization,$loginAccount,$id,$employeeUriString,$name,$description);
    }

    public static final function generateId(string $id): string
    {
        return Str::of($id)
            ->lower()
            ->slug('-');
    }
    public static function generateUri(string $id):RealResourceUriContract
    {
        return URI::realResource('organizations',static::LDOG_CLASS,
            static::generateId($id));
    }
    public static function checkIfEmployeeExist(string $uri):bool
    {
        return URI::isUriExist($uri);
    }
}