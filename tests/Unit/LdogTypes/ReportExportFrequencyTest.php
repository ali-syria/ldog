<?php


namespace AliSyria\LDOG\Tests\Unit\LdogTypes;


use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\OntologyManager\OntologyManager;
use AliSyria\LDOG\Tests\TestCase;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use AliSyria\LDOG\Utilities\LdogTypes\ReportExportFrequency;

class ReportExportFrequencyTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        GS::getConnection()->clearAll();
        OntologyManager::importLdogOntology();
    }

    /**
     * @dataProvider reportExportFrequenciesProvider
     */
    public function testGetAllReportExportFrequencies(string $uri,string $label)
    {
        $dataExporterTarget=ReportExportFrequency::all()->where('uri',$uri)->first();
        $this->assertEquals(new ReportExportFrequency($uri,$label),$dataExporterTarget);
    }

    public function reportExportFrequenciesProvider():array
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;

        return [
            'Hourly'=>[$ldogPrefix.'Hourly','Hourly'],
            'Daily'=>[$ldogPrefix.'Daily','Daily'],
            'Weekly'=>[$ldogPrefix.'Weekly','Weekly'],
            'Monthly'=>[$ldogPrefix.'Monthly','Monthly'],
            'Yearly'=>[$ldogPrefix.'Yearly','Yearly'],
        ];
    }
}