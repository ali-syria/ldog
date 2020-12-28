<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\Facades\URI;
use AliSyria\LDOG\GraphStore\GraphDbDriver;
use AliSyria\LDOG\OrganizationManager\Cabinet;
use AliSyria\LDOG\OrganizationManager\Ministry;
use AliSyria\LDOG\Reconciliation\GraphDbLuceneDriver;
use AliSyria\LDOG\Reconciliation\ReconcilatiationTerm;
use AliSyria\LDOG\Tests\TestCase;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use Illuminate\Support\Collection;
use EasyRdf\Resource;
use Illuminate\Support\Facades\Artisan;

class GraphDbLuceneDriverTest extends TestCase
{
    private GraphDbDriver $graphDB;
    private GraphDbLuceneDriver $graphDbLucene;

    public function setUp(): void
    {
        parent::setUp();
        $this->graphDB=app(GraphDbDriver::class)->connect('open');
        $this->graphDB->clearAll();
        $this->graphDbLucene=app(GraphDbLuceneDriver::class);
        Artisan::call('ldog:init-graphdb-lucene');
    }

    public function testGetInitGraphDbLuceneCommand()
    {

    }

    public function testGetLuceneIndexName()
    {

    }

    public function testReconcile()
    {
        $topograhyPrefix=URI::ontology('topography','location')->getBasueUri()."#";
        $rdfsPrefix=UriBuilder::PREFIX_RDFS;

        $cabinet=Cabinet::create(null,'Syrian Cabinet','The Cabinet of Syria',
            'http://assets.cabinet.sy/logo.png');
        $ministryHealth=Ministry::create($cabinet,'Ministry Of Health','The Health Ministry of Syria',
            'http://assets.cabinet.sy/health/logo.png');
        $organizationGraph="http://organizations.".config('ldog.domain');
        $topographyGraph="http://topography.".config('ldog.domain');
        $healtMinistryUri=$ministryHealth->getUri();
        $damascusUri=URI::realResource('topography','City','damascus')->getResourceUri();

        $this->graphDB->rawUpdate("
            PREFIX rdfs: <$rdfsPrefix>
            PREFIX topography: <$topograhyPrefix>
            
            INSERT DATA {
            
                GRAPH <$topographyGraph> {
                    <$damascusUri>  a topography:City ;
                                    rdfs:label 'damascus' .
                }
                                
                GRAPH <$organizationGraph> {
                    <$healtMinistryUri> topography:city 'damascus' .
                }
            }
        ");

        $this->graphDbLucene->reconcile($healtMinistryUri,$topograhyPrefix.'city',
            'damascus',$damascusUri,$organizationGraph);


        $healthQuery="
            PREFIX topography: <$topograhyPrefix>
        
            SELECT ?city
            WHERE {
                GRAPH <$organizationGraph> {
                    <$healtMinistryUri> topography:city ?city .
                }
            }
        ";

        $resultSet=$this->graphDB->jsonQuery($healthQuery);
        $cities=[];
        foreach ($resultSet as $result)
        {
            if($result->city instanceof Resource)
            {
                $cities[]=$result->city->getUri();
            }
            else
            {
                $cities[]=$result->city->getValue();
            }
        }
        $this->assertEquals(1,count($cities));
        $this->assertEquals($cities[0],$damascusUri);
    }

    public function testMatchLiteralToUri()
    {
        $topograhyPrefix=URI::ontology('topography','location')->getBasueUri()."#";
        $rdfsPrefix=UriBuilder::PREFIX_RDFS;

        $topographyGraph="http://topography.".config('ldog.domain');
        $damascusUri=URI::realResource('topography','City','damascus')->getResourceUri();

        $this->graphDB->rawUpdate("
            PREFIX rdfs: <$rdfsPrefix>
            PREFIX topography: <$topograhyPrefix>
            
            INSERT DATA {
           
                GRAPH <$topographyGraph> {
                    <$damascusUri> a topography:City ;
                                   rdfs:label 'damascus' .
                }
            }
        ");
        $term=$this->graphDbLucene->match('DamAscus',$topograhyPrefix."City");
        $this->assertEquals($damascusUri,$term->getUri());
        $this->assertEquals('damascus',$term->getLable());
        $this->assertEquals(1.0,$term->getScore());
    }

    public function testSearchForCandidateUris()
    {
        $topograhyPrefix=URI::ontology('topography','location')->getBasueUri()."#";
        $rdfsPrefix=UriBuilder::PREFIX_RDFS;

        $topographyGraph="http://topography.".config('ldog.domain');

        $damascusUri=URI::realResource('topography','City','damascus')->getResourceUri();
        $tartousUri=URI::realResource('topography','City','tartous')->getResourceUri();
        $damascusThawraStreet=URI::realResource('topography','Street','damascus_thawraw')->getResourceUri();
        $tartousThawraStreet=URI::realResource('topography','Street','tartous_thawraw')->getResourceUri();

        $this->graphDB->rawUpdate("
            PREFIX rdfs: <$rdfsPrefix>
            PREFIX topography: <$topograhyPrefix>
            
            INSERT DATA {
           
                GRAPH <$topographyGraph> {
                    <$damascusUri> a topography:City ;
                                   rdfs:label 'damascus' .
                    <$tartousUri> a topography:City ;
                                   rdfs:label 'tartous' .  
                    
                    <$damascusThawraStreet> a  topography:Street ;
                                            rdfs:label 'Thawra Street' ;
                                            topography:city <$damascusUri> .   
                    <$tartousThawraStreet> a  topography:Street ;
                                            rdfs:label 'Thawra Street Tartous' ;
                                            topography:city <$tartousUri> .                                                                         
                }
            }
        ");
        Artisan::call('ldog:init-graphdb-lucene');
        sleep(10);
        $terms=$this->graphDbLucene->search('thawra',$topograhyPrefix."Street");
        $expectedTerms=new Collection([
            new ReconcilatiationTerm($damascusThawraStreet,'Thawra Street',4.610454),
            new ReconcilatiationTerm($tartousThawraStreet,'Thawra Street Tartous',3.6883633),
        ]);

        $this->assertEquals($expectedTerms,$terms);
    }
}