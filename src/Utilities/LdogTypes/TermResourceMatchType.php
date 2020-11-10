<?php


namespace AliSyria\LDOG\Utilities\LdogTypes;


use AliSyria\LDOG\Contracts\Utilities\LdogTypes\LdogType;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use Illuminate\Support\Collection;

class TermResourceMatchType extends LdogType
{
    const FullMatch='FullMatch';
    const PartialMatch='PartialMatch';

    public function __construct(string $uri, string $label, ?string $description = null)
    {
        parent::__construct($uri, $label, $description);
    }

    public static function all(): Collection
    {
        $conversionPrefix=UriBuilder::PREFIX_CONVERSION;
        $rdfsPrefix=UriBuilder::PREFIX_RDFS;
        $termResourceMatchTypeClass=$conversionPrefix."TermResourceMatchType";

        $resultSet=GS::secureConnection()->jsonQuery("
            PREFIX conv: <$conversionPrefix>
            PREFIX rdfs: <$rdfsPrefix>
            
            SELECT ?termResourceMatchType ?label ?description
            WHERE {
                  ?termResourceMatchType a  <$termResourceMatchTypeClass> ;
                              rdfs:label ?label .
                  OPTIONAL {?termResourceMatchType rdfs:comment ?description  . }                         
            }                                       
        ");
        $termResourceMatchTypes=[];
        foreach ($resultSet as $termResourceMatchType)
        {
            $termResourceMatchTypes[]=new self(
                $termResourceMatchType->termResourceMatchType->getUri(),$termResourceMatchType->label->getValue(),
                optional(optional($termResourceMatchType)->description)->getValue()
            );
        }

        return new Collection($termResourceMatchTypes);
    }
}