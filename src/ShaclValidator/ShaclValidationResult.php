<?php


namespace AliSyria\LDOG\ShaclValidator;


use AliSyria\LDOG\Contracts\ShaclValidator\ShaclValidationResultContract;

class ShaclValidationResult implements ShaclValidationResultContract
{

    public array $result;

    /**
     * ShaclValidationResult constructor.
     * @param array $resultJSON_LD
     */
    public function __construct(array $resultJSON_LD)
    {
        $this->result=$resultJSON_LD;
    }
    public function getSeverity(): string
    {
        $severity=$this->result['http://www.w3.org/ns/shacl#resultSeverity'][0]['@id'];

        switch ($severity)
        {
            case 'http://www.w3.org/ns/shacl#Violation':
                return ShaclSeverity::VIOLATION;
                break;
            case 'http://www.w3.org/ns/shacl#Info':
                return ShaclSeverity::INFO;
            case 'http://www.w3.org/ns/shacl#Warning':
                return ShaclSeverity::WARNING;
        }
    }

    public function getFocusNode(): string
    {
        return $this->result['http://www.w3.org/ns/shacl#focusNode'][0]['@id'];
    }

    public function getResultPath(): string
    {
        return $this->result['http://www.w3.org/ns/shacl#resultPath'][0]['@id'];
    }

    public function getValue(): string
    {
        return $this->result['http://www.w3.org/ns/shacl#value'][0]['@value'];
    }

    public function getSourceConstraintComponent(): string
    {
        return $this->result['http://www.w3.org/ns/shacl#sourceConstraintComponent'][0]['@id'];
    }

    public function getSourceShape(): string
    {
        return $this->result['http://www.w3.org/ns/shacl#sourceShape'][0]['@id'];
    }

    public function getMessage(): string
    {
        return $this->result['http://www.w3.org/ns/shacl#resultMessage'][0]['@value'];
    }
}