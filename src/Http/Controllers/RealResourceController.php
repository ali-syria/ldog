<?php


namespace AliSyria\LDOG\Http\Controllers;


use AliSyria\LDOG\UriDereferencer\Dereferencer;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class RealResourceController extends Controller
{
    public function resource(Request $request,string $sector,string $concept,string $reference)
    {
        return redirect(
            Dereferencer::resolveRealResource($request,$sector,$concept,$reference),
            302,$request->headers->all()
        );
    }
    public function page(Request $request,string $concept,string $reference)
    {

    }
    public function data(Request $request,string $concept,string $reference)
    {

    }
}