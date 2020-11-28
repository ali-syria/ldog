<?php


namespace AliSyria\LDOG\Tests\Feature;


use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\OntologyManager\OntologyManager;
use AliSyria\LDOG\Tests\TestCase;

class SparqlEndpointTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        GS::getConnection()->clearAll();
        OntologyManager::importLdogOntology();
    }

    public function testSparqlEndpoint()
    {
        $query = "
            PREFIX ldog: <http://ldog.org/ontologies/2020/8/framework#>
            
            SELECT ?modellingOrganization
            WHERE {
                ?modellingOrganization  a   ldog:BatchDataExporterTarget .
            }
        ";
        $response = $this->withHeader('Accept', 'application/sparql-results+json')
            ->get("sparql?query=" . urlencode($query))
            ->assertOk();
        $results = json_decode($response->getContent(), TRUE)['results'];

        $this->assertEqualsCanonicalizing([
            "http://ldog.org/ontologies/2020/8/framework#ModellingOrganization",
            "http://ldog.org/ontologies/2020/8/framework#AllBranches"   ,
            "http://ldog.org/ontologies/2020/8/framework#AllDepartments" ,
            "http://ldog.org/ontologies/2020/8/framework#AllSectors" ,
        ],collect($results['bindings'])->flatten(1)->pluck('value')->toArray());
    }
}