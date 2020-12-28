<?php


namespace AliSyria\LDOG\Reconciliation;


use AliSyria\LDOG\Console\InitGraphDbLuceneReconciliator;
use AliSyria\LDOG\Contracts\Reconciliation\ReconcilatiationTermContract;
use AliSyria\LDOG\Contracts\Reconciliation\ReconciliatorContract;
use AliSyria\LDOG\GraphStore\GraphDbDriver;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GraphDbLuceneDriver implements ReconciliatorContract
{

    public string $index;
    public string $initCommand;
    public GraphDbDriver $graphDB;

    public function __construct()
    {
        $this->index=config('ldog.reconciliation.index');
        $this->initCommand=app(InitGraphDbLuceneReconciliator::class)->signature;
        $this->graphDB=app(GraphDbDriver::class)->connect('open');
    }

    public function getInitCommand(): string
    {
        return $this->initCommand;
    }

    public function getIndexName(): string
    {
        return $this->index;
    }

    public function reconcile(string $subjectUri, string $predicateUri, string $literal,
                              string $targetUri, string $graphIRI = null): void
    {

        $query="";
        if($graphIRI)
        {
            $query="WITH <$graphIRI> ";
        }

        $query.="
            DELETE {
                <$subjectUri> <$predicateUri> '$literal' .
            }
            INSERT {
                <$subjectUri> <$predicateUri> <$targetUri> .
            }
            
            WHERE {}            
        ";

        $this->graphDB->rawUpdate($query);
    }

    public function match(string $literal, string $classUri): ?ReconcilatiationTermContract
    {
        $literal=Str::lower($literal);
        $rdfsPrefix=UriBuilder::PREFIX_RDFS;
        $indexName=$this->getIndexName();

        $terms=[];
        $resultSet=$this->graphDB->jsonQuery("
            PREFIX rdfs: <$rdfsPrefix>
            
            SELECT ?resource ?label
            WHERE {
                ?resource a <$classUri> ;
                          rdfs:label ?label .
                          
                FILTER (lcase(str(?label)) = '$literal')                      
            }
        ");

        foreach ($resultSet as $result)
        {
            $terms[]=new ReconcilatiationTerm($result->resource->getUri(),$result->label->getValue(),
                1);
            if(count($terms)>1)
            {
                break;
            }
        }

        if(count($terms)!=1)
        {
            return null;
        }
        else
        {
            return $terms[0];
        }
    }

    public function search(string $literal, string $classUri): Collection
    {
        $lucenePrefix=UriBuilder::PREFIX_LUCENE;
        $rdfsPrefix=UriBuilder::PREFIX_RDFS;

        $indexName=$this->getIndexName();

        $terms=[];
        $resultSet=$this->graphDB->jsonQuery("
            PREFIX luc: <$lucenePrefix>
            PREFIX rdfs: <$rdfsPrefix>
            
            SELECT ?resource ?label ?score
            WHERE {
                ?resource luc:$indexName '$literal~' ;
                          luc:score ?score ;
                          a <$classUri> ;
                          rdfs:label ?label .
                          
            }
        ");

        foreach ($resultSet as $result)
        {
            $terms[]=new ReconcilatiationTerm($result->resource->getUri(),$result->label->getValue(),
                $result->score->getValue());
        }

        return (new Collection($terms))->sortByDesc('score');
    }
}