<?php


namespace AliSyria\LDOG\Contracts\ShaclValidator;


abstract class ShaclValidator
{
    public string $basicDataShapePath;
    public string $basicShapeShapePath;

    protected function __construct()
    {
        $this->basicDataShapePath=__DIR__."/../../../shapes/basic-data-shape.ttl";
        $this->basicShapeShapePath=__DIR__."/../../../shapes/basic-shape-shape.ttl";
    }

    abstract public function validateGraph(string $dataGraphPath,string $shapGraphPath):ShaclValidationReportContract;

    final public function basicDataValidation(string $dataGraphPath):ShaclValidationReportContract
    {
        return $this->validateGraph($dataGraphPath,$this->basicDataShapePath);
    }
    final public function basicShapeValidation(string $shapeGraphPath):ShaclValidationReportContract
    {
        return $this->validateGraph($shapeGraphPath,realpath($this->basicShapeShapePath));
    }
}