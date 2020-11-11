<?php


namespace AliSyria\LDOG\PublishingPipeline;


use AliSyria\LDOG\Utilities\LdogTypes\TermResourceMatchType;

class TermResourceMapping
{
    public string $predicate;
    public string $term;
    public string $resource;
    public TermResourceMatchType $matchType;

    public function __construct(string $predicate,string $term,string $resource,
                                TermResourceMatchType $matchType)
    {
        $this->predicate=$predicate;
        $this->term=$term;
        $this->resource=$resource;
        $this->matchType=$matchType;
    }
}