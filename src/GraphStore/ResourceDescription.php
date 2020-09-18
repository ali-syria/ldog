<?php


namespace AliSyria\LDOG\GraphStore;


use AliSyria\LDOG\Contracts\GraphStore\ResourceDescriptionContract;

class ResourceDescription implements ResourceDescriptionContract
{
    private string $mimeType;
    private string $body;

    public function __construct(string $mimeType,string $body)
    {
        $this->mimeType=$mimeType;
        $this->body=$body;
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getBody(): string
    {
        return $this->body;
    }
}