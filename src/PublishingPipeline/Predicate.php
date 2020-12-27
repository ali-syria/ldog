<?php


namespace AliSyria\LDOG\PublishingPipeline;


use AliSyria\LDOG\Normalization\Normalizer;

class Predicate
{
    public string $uri;
    public string $name;
    public ?string $description;
    public int $order;
    public string $dataType;
    public ?string $objectClassUri;
    public int $minCount;
    public int $maxCount;
    public string $validationMessage;
    public ?string $normalizedByFunction;

    public function __construct(string $uri,string $name,?string $description,int $order,string $dataType,
        ?string $objectClassUri,int $minCount,int $maxCount,string $validationMessage,string $normalizedByFunctionUri=null)
    {
        $this->uri=$uri;
        $this->name=$name;
        $this->description=$description;
        $this->order=$order;
        $this->dataType=$dataType;
        $this->objectClassUri=$objectClassUri;
        $this->minCount=$minCount;
        $this->maxCount=$maxCount;
        $this->validationMessage=$validationMessage;
        $this->normalizedByFunction=$normalizedByFunctionUri ? Normalizer::extractTargetMethod($normalizedByFunctionUri):null;
    }
    public function isObjectPredicate():bool
    {
        return filled($this->objectClassUri);
    }
}