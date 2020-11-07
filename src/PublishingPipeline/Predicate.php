<?php


namespace AliSyria\LDOG\PublishingPipeline;


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

    public function __construct(string $uri,string $name,?string $description,int $order,string $dataType,
        ?string $objectClassUri,int $minCount,int $maxCount,string $validationMessage)
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
    }
}