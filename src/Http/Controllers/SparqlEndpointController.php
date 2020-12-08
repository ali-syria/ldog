<?php


namespace AliSyria\LDOG\Http\Controllers;


use AliSyria\LDOG\Facades\GS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class SparqlEndpointController
{
    public function __invoke(Request $req)
    {
        if($req->wantsHTML())
        {
            return view('ldog::sparql.endpoint');
        }
        $endpoint=GS::getConnection()::getSparqlEndpoint();
        $response=Http::asForm()->withHeaders([
            'Accept'=>$req->header('Accept'),
        ])
        ->get($endpoint,$req->all());

        return response($response->body(),$response->status(),$response->headers());
    }
}