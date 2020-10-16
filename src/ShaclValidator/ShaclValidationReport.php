<?php


namespace AliSyria\LDOG\ShaclValidator;


use AliSyria\LDOG\Contracts\ShaclValidator\ShaclValidationReportContract;
use Illuminate\Support\Collection;

class ShaclValidationReport implements ShaclValidationReportContract
{
    protected Collection $reportJsonLdCollection;
    public bool $conforms;
    public Collection $results;

    public function __construct(string $rerportJsonLd)
    {
        $this->reportJsonLdCollection=collect(json_decode($rerportJsonLd,true));
        $report=$this->reportJsonLdCollection
            ->where('@type',["http://www.w3.org/ns/shacl#ValidationReport"])
            ->first();
        $this->conforms=$report['http://www.w3.org/ns/shacl#conforms'][0]['@value'];

        $resultsArr=$this->reportJsonLdCollection->where('@type',["http://www.w3.org/ns/shacl#ValidationResult"]);
        $results=[];
        foreach ($resultsArr as $result)
        {
            $results[]=new ShaclValidationResult($result);
        }
        $this->results=collect($results);
    }

    public function isConforms(): bool
    {
        return $this->conforms;
    }

    public function results(): Collection
    {
        return $this->results;
    }
}