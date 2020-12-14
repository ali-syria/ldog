<?php


namespace AliSyria\LDOG\Utilities\LdogTypes;


use AliSyria\LDOG\Contracts\Utilities\LdogTypes\LdogType;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use Illuminate\Support\Collection;

class ReportExportFrequency extends LdogType
{
    const HOURLY= 'Hourly';
    const DAILY= 'Daily';
    const MONTHLY= 'Monthly';
    const YEARLY= 'Yearly';

    public function __construct(string $uri, string $label, ?string $description = null)
    {
        parent::__construct($uri, $label, $description);
    }

    public static function all(): Collection
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;
        $rdfsPrefix=UriBuilder::PREFIX_RDFS;
        $reportExportFrequencyClass=$ldogPrefix."Frequency";

        $resultSet=GS::openConnection()->jsonQuery("
            PREFIX ldog: <$ldogPrefix>
            PREFIX rdfs: <$rdfsPrefix>
            
            SELECT ?reportExportFrequency ?label ?description
            WHERE {
                  ?reportExportFrequency a  <$reportExportFrequencyClass> ;
                              rdfs:label ?label .
                  OPTIONAL {?reportExportFrequency rdfs:comment ?description  . }                         
            }                                       
        ");
        $reportExportFrequencys=[];
        foreach ($resultSet as $reportExportFrequency)
        {
            $reportExportFrequencys[]=new self(
                $reportExportFrequency->reportExportFrequency->getUri(),$reportExportFrequency->label->getValue(),
                optional(optional($reportExportFrequency)->description)->getValue()
            );
        }

        return new Collection($reportExportFrequencys);
    }
}