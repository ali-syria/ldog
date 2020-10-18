<?php


namespace AliSyria\LDOG\Utilities\LdogTypes;


use AliSyria\LDOG\Contracts\Utilities\LdogTypes\LdogType;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use Illuminate\Support\Collection;

class DataExporterTarget extends LdogType
{

    public function __construct(string $uri, string $label, ?string $description = null)
    {
        parent::__construct($uri, $label, $description);
    }

    public static function all(): Collection
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;
        $rdfsPrefix=UriBuilder::PREFIX_RDFS;
        $dataExporterTargetClass=$ldogPrefix."BatchDataExporterTarget";

        $resultSet=GS::secureConnection()->jsonQuery("
            PREFIX ldog: <$ldogPrefix>
            PREFIX rdfs: <$rdfsPrefix>
            
            SELECT ?dataExporterTarget ?label ?description
            WHERE {
                  ?dataExporterTarget a  <$dataExporterTargetClass> ;
                              rdfs:label ?label .
                  OPTIONAL {?dataExporterTarget rdfs:comment ?description  . }                         
            }                                       
        ");
        $dataExporterTargets=[];
        foreach ($resultSet as $dataExporterTarget)
        {
            $dataExporterTargets[]=new self(
                $dataExporterTarget->dataExporterTarget->getUri(),$dataExporterTarget->label->getValue(),
                optional(optional($dataExporterTarget)->description)->getValue()
            );
        }

        return new Collection($dataExporterTargets);
    }
}