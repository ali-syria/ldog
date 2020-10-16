<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\ShaclValidator\JenaShaclValidator;
use AliSyria\LDOG\ShaclValidator\ShaclValidationReport;
use AliSyria\LDOG\ShaclValidator\ShaclValidationResult;
use AliSyria\LDOG\Tests\TestCase;

class JenaShaclValidatorTest extends TestCase
{
    public JenaShaclValidator $validator;
    protected string $validationRportJSONLD='
        [{"@type":["http://www.w3.org/ns/shacl#ValidationReport"],"http://www.w3.org/ns/shacl#conforms":[{"@value":false}],"http://www.w3.org/ns/shacl#result":[{"@id":"_:b1"},{"@id":"_:b2"}]},{"@id":"_:b1","@type":["http://www.w3.org/ns/shacl#ValidationResult"],"http://www.w3.org/ns/shacl#resultSeverity":[{"@id":"http://www.w3.org/ns/shacl#Violation"}],"http://www.w3.org/ns/shacl#sourceConstraintComponent":[{"@id":"http://www.w3.org/ns/shacl#LessThanConstraintComponent"}],"http://www.w3.org/ns/shacl#sourceShape":[{"@id":"_:b3"}],"http://www.w3.org/ns/shacl#focusNode":[{"@id":"http://example.org/ns#Bob"}],"http://www.w3.org/ns/shacl#resultPath":[{"@id":"http://schema.org/birthDate"}],"http://www.w3.org/ns/shacl#value":[{"@value":"1971-07-07","@type":"http://www.w3.org/2001/XMLSchema#date"}],"http://www.w3.org/ns/shacl#resultMessage":[{"@value":"LessThan[<http://schema.org/deathDate>]: value node \"1971-07-07\"^^xsd:date is not less than \"1968-09-10\"^^xsd:date"}]},{"@id":"_:b2","@type":["http://www.w3.org/ns/shacl#ValidationResult"],"http://www.w3.org/ns/shacl#resultSeverity":[{"@id":"http://www.w3.org/ns/shacl#Violation"}],"http://www.w3.org/ns/shacl#sourceConstraintComponent":[{"@id":"http://www.w3.org/ns/shacl#NodeConstraintComponent"}],"http://www.w3.org/ns/shacl#sourceShape":[{"@id":"_:b4"}],"http://www.w3.org/ns/shacl#focusNode":[{"@id":"http://example.org/ns#Bob"}],"http://www.w3.org/ns/shacl#value":[{"@id":"http://example.org/ns#BobsAddress"}],"http://www.w3.org/ns/shacl#resultPath":[{"@id":"http://schema.org/address"}],"http://www.w3.org/ns/shacl#resultMessage":[{"@value":"Node at focusNode <http://example.org/ns#BobsAddress>"}]},{"@id":"_:b3"},{"@id":"_:b4"},{"@id":"http://example.org/ns#Bob"},{"@id":"http://example.org/ns#BobsAddress"},{"@id":"http://schema.org/address"},{"@id":"http://schema.org/birthDate"},{"@id":"http://www.w3.org/ns/shacl#LessThanConstraintComponent"},{"@id":"http://www.w3.org/ns/shacl#NodeConstraintComponent"},{"@id":"http://www.w3.org/ns/shacl#ValidationReport"},{"@id":"http://www.w3.org/ns/shacl#ValidationResult"},{"@id":"http://www.w3.org/ns/shacl#Violation"}]
    ';
    protected ShaclValidationReport $validationReport;

    public function setUp(): void
    {
        parent::setUp();
        $this->validator=app(JenaShaclValidator::class);
        $this->validationReport=new ShaclValidationReport($this->validationRportJSONLD);
    }

    public function testValidateGraph()
    {
        $validationReport=$this->validator->validateGraph(__DIR__.'/../Datasets/data.jsonld',
            __DIR__.'/../Datasets/shape.ttl');

        $this->assertFalse($validationReport->isConforms());
        $this->assertEquals($this->validationReport->results(),$validationReport->results());
    }
}