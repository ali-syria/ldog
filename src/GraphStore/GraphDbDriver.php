<?php


namespace AliSyria\LDOG\GraphStore;


use AliSyria\LDOG\Contracts\GraphStore\ConnectionContract;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;

class GraphDbDriver implements ConnectionContract
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
        return $this->client->get("repositories/{$this->repository}",[
            'query'=>$query,
            'limit'=>$limit
        ])->json();
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
}