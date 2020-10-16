<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\ShaclValidator\ShaclSeverity;
use AliSyria\LDOG\ShaclValidator\ShaclValidationResult;
use AliSyria\LDOG\Tests\TestCase;

class ShaclValidationResultTest extends TestCase
{
    protected string $validationResultJSONLD='
            {"@id":"_:b1","@type":["http://www.w3.org/ns/shacl#ValidationResult"],
            "http://www.w3.org/ns/shacl#resultSeverity":[{"@id":"http://www.w3.org/ns/shacl#Violation"}],
            "http://www.w3.org/ns/shacl#focusNode":[{"@id":"http://datashapes.org/dash#Alice"}],
            "http://www.w3.org/ns/shacl#resultPath":[{"@id":"http://datashapes.org/dash#ssn"}],
            "http://www.w3.org/ns/shacl#value":[{"@value":"987-65-432A"}],
            "http://www.w3.org/ns/shacl#resultMessage":[{"@value":"Too many characters","@language":"en"}],
            "http://www.w3.org/ns/shacl#sourceConstraintComponent":[{"@id":"http://www.w3.org/ns/shacl#RegexConstraintComponent"}]}
    ';
    protected ShaclValidationResult $validationResult;

    public function setUp(): void
    {
        parent::setUp();
        $this->validationResult=new ShaclValidationResult(json_decode($this->validationResultJSONLD,true));
    }

    public function testGetSeverity()
    {
        $this->assertEquals(ShaclSeverity::VIOLATION,$this->validationResult->getSeverity());
    }

    public function testGetFocusNode()
    {
        $this->assertEquals('http://datashapes.org/dash#Alice',$this->validationResult->getFocusNode());
    }

    public function testGetResultPath()
    {
        $this->assertEquals('http://datashapes.org/dash#ssn',$this->validationResult->getResultPath());
    }

    public function testGetValue()
    {
        $this->assertEquals('987-65-432A',$this->validationResult->getValue());
    }

    public function testGetSourceConstraintComponent()
    {
        $this->assertEquals('http://www.w3.org/ns/shacl#RegexConstraintComponent',$this->validationResult->getSourceConstraintComponent());
    }

    public function testGetSourceShape()
    {

    }

    public function testGetMessage()
    {
        $this->assertEquals('Too many characters',$this->validationResult->getMessage());
    }
}