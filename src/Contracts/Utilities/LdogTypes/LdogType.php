<?php


namespace AliSyria\LDOG\Contracts\Utilities\LdogTypes;


use Illuminate\Support\Collection;

abstract class LdogType
{
    public string $uri;
    public string $label;
    public ?string $description;

    protected function __construct(string $uri,string $label,?string $description=null)
    {
        $this->uri=$uri;
        $this->label=$label;
        $this->description=$description;
    }

    abstract public static function all():Collection;

    public static function find(string $uri):self
    {
        return static::all()->where('uri',$uri)->first();
    }
}