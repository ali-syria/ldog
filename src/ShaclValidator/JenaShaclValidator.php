<?php


namespace AliSyria\LDOG\ShaclValidator;


use AliSyria\LDOG\Contracts\ShaclValidator\ShaclValidationReportContract;
use AliSyria\LDOG\Contracts\ShaclValidator\ShaclValidator;
use EasyRdf\Graph;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class JenaShaclValidator extends ShaclValidator
{

    public function __construct()
    {
        parent::__construct();
    }

    public function validateGraph(string $dataGraphPath, string $shapGraphPath): ShaclValidationReportContract
    {
//        exec("shacl validate --shapes=\"".$shapGraphPath."\" --data=\"".$dataGraphPath."\"",$output,$retur);
//        dd(implode(" ",$output));


        $validationProcess=new Process(['shacl','validate','--shapes',$shapGraphPath,'--data',$dataGraphPath]);
        $validationProcess->setEnv([
            'JENA_HOME'=>'D:\Binaries\apache-jena-3.16.0'
        ])->run();
//
        if (!$validationProcess->isSuccessful()) {
            throw new ProcessFailedException($validationProcess);
        }
//
//        dd($validationProcess->getOutput());
        $graph=new Graph(null);
        $graph->parse($validationProcess->getOutput(),'turtle',null);
        $format = \EasyRdf\Format::getFormat('jsonld');
        $result=$graph->serialise($format);

        return new ShaclValidationReport($result);
    }
}