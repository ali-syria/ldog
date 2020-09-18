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
    public function page(Request $request,string $sector,string $concept,string $reference)
    {
        return Dereferencer::resourceToHtmlResponse(
            $sector,$concept,$reference,$request->header('Accept')
        );
    }
    public function data(Request $request,string $sector,string $concept,string $reference)
    {
        return Dereferencer::resourceToRdfResponse(
            $sector,$concept,$reference,$request->header('Accept')
        );
    }
}