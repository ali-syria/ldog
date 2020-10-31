<?php


namespace AliSyria\LDOG\Contracts\GraphStore;


interface GraphManagementContract
{
    public function fetchNamedGraph(string $namedGraphUri,string $mimeType="application/n-quads");
}