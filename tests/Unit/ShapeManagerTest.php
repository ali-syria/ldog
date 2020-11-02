<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\Facades\URI;
use AliSyria\LDOG\ShaclValidator\ShaclValidationReport;
use AliSyria\LDOG\ShapesManager\DataShape;
use AliSyria\LDOG\ShapesManager\ShapeManager;
use AliSyria\LDOG\Tests\TestCase;
use AliSyria\LDOG\UriBuilder\UriBuilder;

class ShapeManagerTest extends TestCase
{
    protected string $shapeUrl="http://api.eresta.test/shapes/HealthFacility.ttl";
    protected string $validationReportJSONLD="[{\"@type\":[\"http://www.w3.org/ns/shacl#ValidationReport\"],\"http://www.w3.org/ns/shacl#conforms\":[{\"@value\":true}]},{\"@id\":\"http://www.w3.org/ns/shacl#ValidationReport\"}]";

    public function setUp(): void
    {
        parent::setUp();
        GS::getConnection()->clearAll();
        GS::getConnection()
            ->loadIRIintoNamedGraph('http://api.eresta.test/ontology/ldog.ttl','http://ldog.com/ontology');
    }

    public function testImportShapeFromUrl()
    {
        $namespace=UriBuilder::PREFIX_LDOG;
        $url=$this->shapeUrl;
        $prefix='health-facility-shape';

        $dataShape=ShapeManager::importFromUrl($url,'health',$prefix);

        $graphUri=ShapeManager::generateUri('health',$prefix);

        $this->assertTrue(ShapeManager::checkIfExist($graphUri));
        $this->assertInstanceOf(DataShape::class,$dataShape);

        $ldogPrefix=UriBuilder::PREFIX_LDOG;
        $resultSet=GS::getConnection()->jsonQuery("
             PREFIX ldog: <$ldogPrefix>
             
             SELECT ?prefix
                WHERE {
                    GRAPH <$graphUri> { 
                        <$namespace> a ldog:DataShape;
                                     ldog:prefix ?prefix .
                    }                
                }  
        ");

        foreach ($resultSet as $result)
        {
            $this->assertEquals($prefix,$result->prefix->getValue());
            break;
        }

        return $dataShape;
    }

    public function testRetrieveDataShape()
    {
        $url=$this->shapeUrl;
        $prefix='health-facility-shape';

        $expectedDataShape=ShapeManager::importFromUrl($url,'health',$prefix);

        $this->assertEquals($expectedDataShape,ShapeManager::retrieve($expectedDataShape->getUri()));
    }
    public function testCheckIfShapeExist()
    {
        $graphUri=URI::dataShape('health','health-facility-shape')->getBasueUri();
        $ldogPrefix=UriBuilder::PREFIX_LDOG;

        GS::getConnection()->rawUpdate(
            "
            PREFIX ldog: <$ldogPrefix>
            
            INSERT DATA
                    { 
                      GRAPH <$graphUri> {
                                <$graphUri> a ldog:DataShape .                      
                      }
                    }"
        );

        $this->assertTrue(ShapeManager::checkIfExist($graphUri));
        $this->assertFalse(ShapeManager::checkIfExist($graphUri."#fff"));
    }

    public function testGenerateShapeUri()
    {
        $shapeUri=ShapeManager::generateUri('health','health-facility-shape');
        $this->assertEquals('http://health.'.config('ldog.domain').'/shape/health-facility-shape',
            $shapeUri);
    }

    public function testValidateShape()
    {
        $actualValidationReport=ShapeManager::validateShape($this->shapeUrl);
        $expectedValidationReport=new ShaclValidationReport($this->validationReportJSONLD);

        $this->assertEquals($expectedValidationReport,$actualValidationReport);
    }
}