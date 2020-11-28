<?php


namespace AliSyria\LDOG\TemplateBuilder;


use AliSyria\LDOG\Contracts\OrganizationManager\ModellingOrganizationContract;
use AliSyria\LDOG\Contracts\ShapesManager\DataShapeContract;
use AliSyria\LDOG\Contracts\TemplateBuilder\DataCollectionTemplateContract;
use AliSyria\LDOG\Contracts\TemplateBuilder\DataTemplate;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\Facades\URI;
use AliSyria\LDOG\OrganizationManager\Organization;
use AliSyria\LDOG\OrganizationManager\OrganizationFactory;
use AliSyria\LDOG\ShapesManager\ShapeManager;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use AliSyria\LDOG\Utilities\LdogTypes\DataDomain;
use AliSyria\LDOG\Utilities\LdogTypes\DataExporterTarget;

class DataCollectionTemplate extends DataTemplate implements DataCollectionTemplateContract
{
    public function __construct(string $uri,string $label, string $description, DataShapeContract $dataShape,
         ModellingOrganizationContract $modellingOrganization, DataExporterTarget $dataExporterTarget,
         DataDomain $dataDomain)
    {
        parent::__construct($uri,$label, $description, $dataShape, $modellingOrganization, $dataExporterTarget,
            $dataDomain);
    }

    public static function create(string $identifier,string $label, string $description, DataShapeContract $dataShape,
         ModellingOrganizationContract $modellingOrganization, DataExporterTarget $dataExporterTarget,
         DataDomain $dataDomain,string $silkLslSpecs=null)
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;
        $rdfsPrefix=UriBuilder::PREFIX_RDFS;
        $rdfPrefix=UriBuilder::PREFIX_RDF;
        $templateUri=URI::template($dataDomain->subDomain,$identifier)->getUri();
        $silkLslSpecsTriple="";
        if(!is_null($silkLslSpecs))
        {
            $silkLslSpecsTriple="; ldog:silkLslSpecs '''$silkLslSpecs''' ^^rdf:XMLLiteral";
        }

        $query="
            PREFIX ldog: <$ldogPrefix>
            PREFIX rdfs: <$rdfsPrefix>
            PREFIX rdf: <$rdfPrefix>
            
            INSERT DATA 
            {
                <$templateUri> a ldog:DataCollectionTemplate;
                        rdfs:label '$label' ;
                        rdfs:comment '$description' ;
                        ldog:hasShape <{$dataShape->getUri()}> ; 
                        ldog:isDataCollectionTemplateOf <{$modellingOrganization->getUri()}>;
                        ldog:shouldDataCollectionExportedBy <{$dataExporterTarget->uri}> ;
                        ldog:dataDomain     <{$dataDomain->uri}> 
                        $silkLslSpecsTriple .
            }  
        ";
        GS::getConnection()->rawUpdate($query);

        return new self($templateUri,$label,$description,$dataShape,$modellingOrganization,
            $dataExporterTarget,$dataDomain);
    }

    public static function retrieve(string $uri): ?DataTemplate
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;
        $rdfsPrefix=UriBuilder::PREFIX_RDFS;

        $query="
            PREFIX ldog: <$ldogPrefix>
            PREFIX rdfs: <$rdfsPrefix>
            
            SELECT ?label ?description ?dataShape ?modellingOrganization ?dataExporterTarget ?dataDomain
            WHERE {
                    <$uri> a ldog:DataCollectionTemplate;
                    rdfs:label ?label ;
                    rdfs:comment ?description ;
                    ldog:hasShape ?dataShape ; 
                    ldog:isDataCollectionTemplateOf ?modellingOrganization;
                    ldog:shouldDataCollectionExportedBy ?dataExporterTarget ;
                    ldog:dataDomain  ?dataDomain .
            } 
        ";
        $resultSet=GS::getConnection()->jsonQuery($query);

        foreach ($resultSet as $result)
        {
            $label=$result->label->getValue();
            $description=optional(optional($result)->description)->getValue();
            $dataExporterTarget=DataExporterTarget::find($result->dataExporterTarget->getUri());
            $dataDomain=DataDomain::find($result->dataDomain->getUri());
            $dataShape=ShapeManager::retrieve($result->dataShape->getUri());
            $modellingOrganization=OrganizationFactory::retrieveByUri($result->modellingOrganization->getUri());
            return new self($uri,$label,$description,$dataShape,$modellingOrganization,
                $dataExporterTarget,$dataDomain);
        }
        return null;
    }
}