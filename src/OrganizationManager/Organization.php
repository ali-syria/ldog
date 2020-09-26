<?php


namespace AliSyria\LDOG\OrganizationManager;


use AliSyria\LDOG\Contracts\OrganizationManager\EmployeeContract;
use AliSyria\LDOG\Contracts\OrganizationManager\HasParentContract;
use AliSyria\LDOG\Contracts\OrganizationManager\OrganizationContract;
use AliSyria\LDOG\Contracts\OrganizationManager\WeakOrganizationContract;
use AliSyria\LDOG\Contracts\UriBuilder\RealResourceUriContract;
use AliSyria\LDOG\Exceptions\OrganizationAlreadyExist;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\Facades\URI;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

abstract class Organization implements OrganizationContract
{
    private string $uri;
    private string $name;
    private string $description;
    private ?string $logoUrl;
    private ?OrganizationContract $parentOrganization;

    public function __construct(string $uri,string $name,string $description,?string $logoUrl)
    {
        $this->uri=$uri;
        $this->name=$name;
        $this->description=$description;
        $this->logoUrl=$logoUrl;
    }

    public function getUri():string
    {
        return $this->uri;
    }
    public static function getLdogClass():string
    {
        return static::LDOG_CLASS;
    }
    public static function getLdogParentProperty():string
    {
        return static::LDOG_PARENT_PROPERTY;
    }
    public static function checkIfOrganizationExist(string $uri):bool
    {
        return URI::isUriExist($uri);
    }
    public static function create(?OrganizationContract $parentOrganization,string $name,
         string $description,string $logoUrl=null):self
    {
        $hasParent=in_array(HasParentContract::class,class_implements(static::class));
        $isWeak=in_array(WeakOrganizationContract::class,class_implements(static::class));

        throw_if($hasParent&& is_null($parentOrganization),
            new \RuntimeException('parent organization is required'));

        $ldogClass=static::getLdogClass();
        if($isWeak)
        {
            $organizationUri=static::generateSubUri($name,$parentOrganization);
            $organizationsGraph=$organizationUri->getSectorUri();
            $organizationUriString=$organizationUri->getSubResourceUri();
        }
        else
        {
            $organizationUri=static::generateUri($name);
            $organizationsGraph=$organizationUri->getSectorUri();
            $organizationUriString=$organizationUri->getResourceUri();
        }

        $ldogPrefix=UriBuilder::PREFIX_LDOG;
        $xsdPrefix=UriBuilder::PREFIX_XSD;

        throw_if(static::checkIfOrganizationExist($organizationUriString),
            new OrganizationAlreadyExist('Organization Alraedy Exists'));

        $parentQuery="";
        if($hasParent)
        {
            $parentOrganizationUri=$parentOrganization->getUri();
            $ldogParentProperty=self::getLdogParentProperty();
            $parentQuery="<$organizationUriString> ldog:$ldogParentProperty <$parentOrganizationUri>.";
        }

        $query="
            PREFIX ldog: <$ldogPrefix>
            PREFIX xsd: <$xsdPrefix>
            
            INSERT DATA 
            {
                GRAPH <$organizationsGraph> {
                    <$organizationUriString> a ldog:$ldogClass ;
                                       ldog:name '$name' ;
                                       ldog:description '$description' ;
                                       ldog:logo '$logoUrl'^^xsd:anyURI .
                    $parentQuery                                       
                }
            }  
        ";
        GS::getConnection()->rawUpdate($query);

        return new static($organizationUriString,$name,$description,$logoUrl);
    }

    public function getName(): string
    {
        return $this->name;
    }
    public function getDescription(): string
    {
        return $this->description;
    }
    public function getLogoUrl(): ?string
    {
        return $this->logoUrl;
    }

    public function parentOrganization(): ?OrganizationContract
    {
        return $this->parentOrganization;
    }

    public function childOrganizations(): ?Collection
    {
        // TODO: Implement childOrganizations() method.
    }

    public function employees(): Collection
    {
        // TODO: Implement employees() method.
    }

    public function admin(): EmployeeContract
    {
        // TODO: Implement admin() method.
    }

    public static final function generateId(string $name): string
    {
        return Str::of($name)
            ->lower()
            ->slug('-');
    }
    public static function generateUri(string $name,OrganizationContract $parent=null):RealResourceUriContract
    {
        return URI::realResource('organizations',static::getLdogClass(),
            static::generateId($name));
    }
}