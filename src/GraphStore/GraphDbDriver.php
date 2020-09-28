<?php


namespace AliSyria\LDOG\GraphStore;


use AliSyria\LDOG\Contracts\GraphStore\ConnectionContract;
use AliSyria\LDOG\Contracts\GraphStore\GraphManagementContract;
use AliSyria\LDOG\Contracts\GraphStore\GraphUpdateContract;
use AliSyria\LDOG\Contracts\GraphStore\QueryContract;
use AliSyria\LDOG\Contracts\GraphStore\ResourceDescriptionContract;
use AliSyria\LDOG\Facades\GS;
use EasyRdf\Sparql\Result;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class GraphDbDriver implements ConnectionContract,QueryContract,GraphUpdateContract,GraphManagementContract
{
    private string $host;
    private string $repository;
    private string $username;
    private string $password;
    private string $token;
    private PendingRequest $client;

    public function connect(string $connectionConfigKey):self
    {
        $config=config('ldog.graph_stores.'.$connectionConfigKey);
        $this->host=$config['host'];
        $this->repository=$config['repository'];
        $this->username=$config['username'];
        $this->password=$config['password'];

        $this->initializeClient();
        $this->authenticate($this->username,$this->password);

        return $this;
    }

    private function authenticate(string $username,string $password)
    {
        $response= $this->client->withHeaders([
            'X-GraphDB-Password'=>'root'
        ])->post('rest/login/'.$this->username);
        $response->throw();
        $this->token=$response->header('Authorization');
        $this->client->withHeaders([
            'Authorization'=>$this->token,
            'Accept'=>'application/sparql-results+json'
        ]);
    }
    private function initializeClient():PendingRequest
    {
        return $this->client=Http::withOptions([
            'base_uri'=>$this->host
        ]);
    }
    public function query(string $query,int $limit=10)
    {
        $response= $this->client->get("repositories/{$this->repository}",[
            'query'=>$query,
            'limit'=>$limit
        ]);
        $response->throw();
        return $response->json();
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getRepository(): string
    {
        return $this->repository;
    }

    public function getUsername(): string
    {
        return $this->repository;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function loadIRIintoNamedGraph(string $sourceIRI, string $graphIRI)
    {
        $result=$this->client->asForm()->post("repositories/{$this->repository}/statements",[
            'update'=>"LOAD  <$sourceIRI> INTO GRAPH <$graphIRI>",
        ]);
        $result->throw();
    }

    public function clearNamedGraph(string $graphIRI)
    {
        $result=$this->client->asForm()->post("repositories/{$this->repository}/statements",[
            'update'=>"CLEAR GRAPH <$graphIRI>",
        ]);
        $result->throw();
    }
    public function clearAll()
    {
        $result=$this->client->asForm()->post("repositories/{$this->repository}/statements",[
            'update'=>"CLEAR ALL",
        ]);
        $result->throw();
    }

    public function rawQuery(string $query):string
    {
        $response= $this->client->get("repositories/{$this->repository}",[
            'query'=>$query
        ]);
        return $response->body();
    }

    public function jsonQuery(string $query): Result
    {
        return new Result($this->rawQuery($query),'application/sparql-results+json');
    }

    public function rdfQuery(string $query): array
    {
        // TODO: Implement rdfQuery() method.
    }

    public function rawUpdate(string $query): string
    {
        $response= $this->client->asForm()->post("repositories/{$this->repository}/statements",[
            'update'=>$query
        ]);
        $response->throw();

        return $response->body();
    }

    public function describeResource(string $uri,string $mimeType): ResourceDescriptionContract
    {
        $query="describe <$uri>";
        $response=$this->client->withHeaders([
            'Accept'=>$mimeType
        ])->get("repositories/{$this->repository}",[
            'query'=>$query
        ]);
        $response->throw();

        return new ResourceDescription($response->header('Content-Type'),$response->body());
    }

    public function isResourceExist(string $uri): bool
    {
        return GS::getConnection()->jsonQuery("
            ASK { <$uri> ?p ?o }
        ")->getBoolean();
    }
}