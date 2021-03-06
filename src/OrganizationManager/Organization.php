<?php


namespace AliSyria\LDOG\OrganizationManager;


use AliSyria\LDOG\Authentication\User;
use AliSyria\LDOG\BatchImporter\DataCollection;
use AliSyria\LDOG\Contracts\OrganizationManager\EmployeeContract;
use AliSyria\LDOG\Contracts\OrganizationManager\HasParentContract;
use AliSyria\LDOG\Contracts\OrganizationManager\ModellingOrganizationContract;
use AliSyria\LDOG\Contracts\OrganizationManager\OrganizationContract;
use AliSyria\LDOG\Contracts\OrganizationManager\WeakOrganizationContract;
use AliSyria\LDOG\Contracts\UriBuilder\RealResourceUriContract;
use AliSyria\LDOG\Exceptions\OrganizationAlreadyExist;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\Facades\URI;
use AliSyria\LDOG\TemplateBuilder\DataCollectionTemplate;
use AliSyria\LDOG\TemplateBuilder\ReportTemplate;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use AliSyria\LDOG\Utilities\LdogTypes\DataExporterTarget;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

abstract class Organization implements OrganizationContract
{
    private string $uri;
    private string $name;
    private string $description;
    private ?string $logoUrl;

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
        $isModellingOrganization=in_array(ModellingOrganizationContract::class,class_implements(static::class));

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

        $organizationRoleClass='DataSourceOrganization';
        if($isModellingOrganization)
        {
            $organizationRoleClass='ModellingOrganization';
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
            $parentQuery="<$organizationUriString> ldog:$ldogParentProperty <$parentOrganizationUri> .";
        }

        $query="
            PREFIX ldog: <$ldogPrefix>
            PREFIX xsd: <$xsdPrefix>
            
            INSERT DATA 
            {
                GRAPH <$organizationsGraph> {
                    <$organizationUriString> a ldog:$ldogClass ;
                                       a ldog:$organizationRoleClass;
                                       ldog:name '$name' ;
                                       ldog:description '$description' ;
                                       ldog:logo '$logoUrl'^^xsd:anyURI .
                    $parentQuery } }  
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
        $ldogPrefix=UriBuilder::PREFIX_LDOG;
        $organizationUri=$this->getUri();

        $resultSet=GS::openConnection()->jsonQuery("
            PREFIX ldog: <$ldogPrefix>
            
            SELECT ?parentOrganization ?class ?name ?description ?logo
            WHERE {
                  <$organizationUri> ldog:subOrganizationOf ?parentOrganization.
                  ?parentOrganization  a ?class ;
                          ldog:name ?name ;
                          ldog:description ?description .
                  OPTIONAL {?parentOrganization ldog:logo ?logo  . }                         
            }                                       
        ");
        $organization=null;
        foreach ($resultSet as $result)
        {
            try{
                $class=OrganizationFactory::resolveLdogClassUriToClass($result->class->getUri());
            }
            catch (\RuntimeException $e)
            {
                continue;
            }
            if(optional($result)->parentOrganization)
            {
                $organization= new $class($result->parentOrganization->getUri(),$result->name->getValue(),$result->description->getValue(),
                    optional(optional($result)->logo)->getValue());
            }
            break;
        }

        return $organization;
    }

    public function childOrganizations(): ?Collection
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;
        $organizationUri=$this->getUri();

        $resultSet=GS::openConnection()->jsonQuery("
            PREFIX ldog: <$ldogPrefix>
            
            SELECT ?childOrganization ?class ?name ?description ?logo
            WHERE {
                  ?childOrganization ldog:subOrganizationOf  <$organizationUri> .
                  ?childOrganization  a ?class ;
                          ldog:name ?name ;
                          ldog:description ?description .
                  OPTIONAL {?childOrganization ldog:logo ?logo  . }                         
            }                                       
        ");
        $organizations=[];
        foreach ($resultSet as $result)
        {
            if(optional($result)->childOrganization)
            {
                try{
                    $class=OrganizationFactory::resolveLdogClassUriToClass($result->class->getUri());
                }
                catch (\RuntimeException $e)
                {
                    continue;
                }
                $organizations[]= new $class($result->childOrganization->getUri(),$result->name->getValue(),$result->description->getValue(),
                    optional(optional($result)->logo)->getValue());
            }
        }

        return new Collection($organizations);
    }
    public function dataTemplatesForExport(): Collection
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;
        $modellingOrganizationUri=$this->getUri();
        $isModellingOrganization=in_array(ModellingOrganizationContract::class,class_implements(static::class));
        $isBranch=$this instanceof Branch;
        $isDepartment=$this instanceof Department;
//        $exportTargets=DataExporterTarget::all();

        if(!$isModellingOrganization)
        {
            $modellingOrganizationUri=$this->parentOrganization()->getUri();
        }
        $query="
            PREFIX ldog: <$ldogPrefix>
            
            SELECT ?dataTemplate ?templateClass ?exportTarget
            WHERE {
                  ?dataTemplate ldog:isDataCollectionTemplateOf <$modellingOrganizationUri> ;
                                 a ?templateClass ;
                                 ldog:shouldBatchExportedBy ?exportTarget .                                      
            }   
        ";

        $resultSet=GS::openConnection()->jsonQuery($query);

        $dataTemplates=[];
        foreach ($resultSet as $result)
        {
            $classUri=$result->templateClass->getUri();
            $templateUri=$result->dataTemplate->getUri();
            $exportTarget=$result->exportTarget->getUri();
            if($isModellingOrganization)
            {
                if($exportTarget!==$ldogPrefix.DataExporterTarget::MODELLING_ORGANIZATION)
                {
                    continue;
                }
            }
            else
            {
                if($isBranch && !in_array($exportTarget,[
                        $ldogPrefix.DataExporterTarget::ALL_SECTORS,
                        $ldogPrefix.DataExporterTarget::ALL_BRANCHES,
                    ]))
                {
                    continue;
                }
                elseif ($isDepartment && !in_array($exportTarget,[
                        $ldogPrefix.DataExporterTarget::ALL_SECTORS,
                        $ldogPrefix.DataExporterTarget::ALL_DEPARTMENTS,
                    ]))
                {
                    continue;
                }
            }

            if($classUri==$ldogPrefix.'ReportTemplate')
            {
                $dataTemplates[]=ReportTemplate::retrieve($templateUri);
            }
            elseif($classUri==$ldogPrefix.'DataCollectionTemplate')
            {
                $dataTemplates[]=DataCollectionTemplate::retrieve($templateUri);
            }
            else
            {
                continue;
            }
        }

        return new Collection($dataTemplates);
    }
    public function departments()
    {
        return $this->childOrganizations()
            ->filter(fn(Organization $org)=>$org instanceof Department);
    }
    public function branches()
    {
        return $this->childOrganizations()
            ->filter(fn(Organization $org)=>$org instanceof Branch);
    }
    public function dataTemplates():Collection
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;
        $organizationUri=$this->getUri();

        $resultSet=GS::openConnection()->jsonQuery("
            PREFIX ldog: <$ldogPrefix>
            
            SELECT ?dataTemplate ?class
            WHERE {
                  ?dataTemplate ldog:isDataCollectionTemplateOf <$organizationUri> ;
                                 a ?class .                                       
            }                                       
        ");
        $dataTemplates=[];
        foreach ($resultSet as $result)
        {
            $classUri=$result->class->getUri();
            $templateUri=$result->dataTemplate->getUri();

            if($classUri==$ldogPrefix.'ReportTemplate')
            {
                $dataTemplates[]=ReportTemplate::retrieve($templateUri);
            }
            elseif($classUri==$ldogPrefix.'DataCollectionTemplate')
            {
                $dataTemplates[]=DataCollectionTemplate::retrieve($templateUri);
            }
            else
            {
                continue;
            }
        }

        return new Collection($dataTemplates);
    }
    public function employees(): Collection
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;
        $organizationUri=$this->getUri();

        $resultSet=GS::getConnection()->jsonQuery("
            PREFIX ldog: <$ldogPrefix>
            
            SELECT ?employee ?username ?id ?name ?description
            WHERE {
                ?employee a ldog:Employee ;
                      ldog:hasLoginAccount ?loginAccount;
                      ldog:isEmployeeOf <$organizationUri> ;
                      ldog:id ?id ;
                      ldog:name ?name ;
                      ldog:description ?description .   
                ?loginAccount ldog:username ?username. 
            }
        ");

        $employees=[];
        foreach ($resultSet as $result)
        {
            if(optional($result)->employee)
            {
                $loginAccount=User::retrieve($result->username->getValue());
                $employees[]= new Employee($this,$loginAccount,$result->id->getValue(),
                    $result->employee->getUri(),$result->name->getValue(),
                    $result->description->getValue());
            }
        }

        return new Collection($employees);
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