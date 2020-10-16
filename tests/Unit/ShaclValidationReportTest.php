<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\ShaclValidator\ShaclValidationReport;
use AliSyria\LDOG\ShaclValidator\ShaclValidationResult;
use AliSyria\LDOG\Tests\TestCase;
use Illuminate\Support\Collection;

class ShaclValidationReportTest extends TestCase
{
    protected string $validationRportJSONLD='
    [
        {
            "@type":["http://www.w3.org/ns/shacl#ValidationReport"],
            "http://www.w3.org/ns/shacl#conforms":[{"@value":false}],
            "http://www.w3.org/ns/shacl#result":[{"@id":"_:b1"}]
        },
        {
            "@id":"_:b1","@type":["http://www.w3.org/ns/shacl#ValidationResult"],
            "http://www.w3.org/ns/shacl#resultSeverity":[{"@id":"http://www.w3.org/ns/shacl#Violation"}],
            "http://www.w3.org/ns/shacl#focusNode":[{"@id":"http://datashapes.org/dash#Alice"}],
            "http://www.w3.org/ns/shacl#resultPath":[{"@id":"http://datashapes.org/dash#ssn"}],
            "http://www.w3.org/ns/shacl#value":[{"@value":"987-65-432A"}],
            "http://www.w3.org/ns/shacl#resultMessage":[{"@value":"Too many characters","@language":"en"}]
        }
    ]
    ';
    protected array $validationRportArray;
    protected ShaclValidationReport $validationReport;

    public function setUp(): void
    {
        parent::setUp();
        $this->validationRportArray=json_decode($this->validationRportJSONLD,true);
        $this->validationReport=new ShaclValidationReport($this->validationRportJSONLD);
    }

    public function testIsConforms()
    {
        $this->assertFalse($this->validationReport->conforms);
    }

    public function testValidationResults()
    {
        $collection=collect($this->validationRportArray);
        $report=$collection
            ->where('@type',["http://www.w3.org/ns/shacl#ValidationReport"])
            ->first();
        $results=$collection->where('@type',["http://www.w3.org/ns/shacl#ValidationResult"]);
        $expectedResults=[];
        foreach ($results as $result)
        {
            $expectedResults[]=new ShaclValidationResult($result);
        }
        $this->assertEquals(collect($expectedResults),$this->validationReport->results());
    }
}