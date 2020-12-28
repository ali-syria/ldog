<?php


namespace AliSyria\LDOG\Reconciliation;


use AliSyria\LDOG\Contracts\Reconciliation\ReconcilatiationTermContract;
use Illuminate\Support\Collection;

class ReconcilatiationTerm implements ReconcilatiationTermContract
{

    public string $uri;
    public string $label;
    public float $score;

    public function __construct(string $resourceUri,string $label,float $score)
    {
        $this->uri=$resourceUri;
        $this->label=$label;
        $this->score=$score;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getLable(): string
    {
        return $this->label;
    }

    public function getScore(): float
    {
        return $this->score;
    }
}