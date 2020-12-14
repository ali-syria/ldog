<?php


namespace AliSyria\LDOG\Utilities\LdogTypes;


use AliSyria\LDOG\Contracts\Utilities\LdogTypes\LdogType;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use Illuminate\Support\Collection;

class DataDomain extends LdogType
{
    public string $subDomain;

    const EDUCATION= 'Education';
    const GENERALINFO= 'GeneralInfo';
    const GOVERNAMENT= 'Governament';
    const HEALTH= 'Health';
    const REALESTATE= 'RealEstate';
    const TOURISM= 'Tourism';
    const TRANSPORT='Transport';

    public function __construct(string $uri, string $label, ?string $description = null,string $subDomain)
    {
        parent::__construct($uri, $label, $description);
        $this->subDomain=$subDomain;
    }

    public static function all(): Collection
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;
        $rdfsPrefix=UriBuilder::PREFIX_RDFS;
        $dataDomainClass=$ldogPrefix."DataDomain";

        $resultSet=GS::openConnection()->jsonQuery("
            PREFIX ldog: <$ldogPrefix>
            PREFIX rdfs: <$rdfsPrefix>
            
            SELECT ?dataDomain ?label ?description ?subDomain
            WHERE {
                  ?dataDomain a  <$dataDomainClass> ;
                              rdfs:label ?label ;
                              ldog:subDomain ?subDomain .
                  OPTIONAL {?dataDomain rdfs:comment ?description  . }                         
            }                                       
        ");
        $dataDomains=[];
        foreach ($resultSet as $dataDomain)
        {
            $dataDomains[]=new self(
                $dataDomain->dataDomain->getUri(),$dataDomain->label->getValue(),
                optional(optional($dataDomain)->description)->getValue(),$dataDomain->subDomain->getValue(),
            );
        }

        return new Collection($dataDomains);
    }

}