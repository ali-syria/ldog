<?php


namespace AliSyria\LDOG\Reconciliation;


use AliSyria\LDOG\Contracts\Reconciliation\ReconcilatiationTermContract;
use Illuminate\Support\Collection;

class ReconcilatiationTerm implements ReconcilatiationTermContract
{

    private string $resourceUri;
    private string $label;
    private float $score;

    public function __construct(string $resourceUri,string $label,float $score)
    {
        $this->resourceUri=$resourceUri;
        $this->label=$label;
        $this->score=$score;
    }

    public function getResourceUri(): string
    {
        return $this->resourceUri;
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