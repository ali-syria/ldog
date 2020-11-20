<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\Facades\URI;
use AliSyria\LDOG\OntologyManager\OntologyManager;
use AliSyria\LDOG\Tests\TestCase;
use AliSyria\LDOG\UriBuilder\UriBuilder;

class OntologyManagerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        GS::getConnection()->clearAll();
        OntologyManager::importLdogOntology();
    }

    public function testImportFromUrl()
    {
        $namespace=UriBuilder::PREFIX_LDOG;
        $url="http://api.eresta.test/ontology/ldog.ttl";
        $prefix='ldog';

        OntologyManager::importFromUrl($url,'organizations',$prefix,$namespace);

        $graphUri=OntologyManager::generateUri('organizations',$prefix);

        $this->assertTrue(OntologyManager::checkIfExist($graphUri));

        $ldogPrefix=UriBuilder::PREFIX_LDOG;
        $resultSet=GS::getConnection()->jsonQuery("
             PREFIX ldog: <$ldogPrefix>
             
             SELECT ?prefix
                WHERE {
                    GRAPH <$graphUri> { 
                        <$namespace> a ldog:Ontology;
                                     ldog:prefix ?prefix .
                    }                
                }  
        ");

        foreach ($resultSet as $result)
        {
            $this->assertEquals($prefix,$result->prefix->getValue());
            break;
        }
    }
    public function testGenerateOntologyUri()
    {
        $ontologyUri=OntologyManager::generateUri('organizations','ldog');
        $this->assertEquals('http://organizations.'.config('ldog.domain').'/ontology/ldog',
            $ontologyUri);
    }
    public function testCheckIfOntologyExist()
    {
        $graphUri=URI::ontology('transport','vehicle')->getBasueUri();
        $ldogPrefix=UriBuilder::PREFIX_LDOG;

        GS::getConnection()->rawUpdate(
            "
            PREFIX ldog: <$ldogPrefix>
            
            INSERT DATA
                    { 
                      GRAPH <$graphUri> {
                                <$graphUri> a ldog:Ontology .                      
                      }
                    }"
        );

        $this->assertTrue(OntologyManager::checkIfExist($graphUri));
        $this->assertFalse(OntologyManager::checkIfExist($graphUri."#fff"));
    }
}