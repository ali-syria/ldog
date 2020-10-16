<?php


namespace AliSyria\LDOG\Contracts\ShaclValidator;


interface ShaclValidationResultContract
{
    public function getSeverity():string;
    public function getFocusNode():string ;
    public function getResultPath():string ;
    public function getValue():string ;
    public function getSourceConstraintComponent():string ;
    public function getSourceShape():string ;
    public function getMessage():string ;
}