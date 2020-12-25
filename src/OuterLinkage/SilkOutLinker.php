<?php


namespace AliSyria\LDOG\OuterLinkage;


use AliSyria\LDOG\Contracts\OuterLinkage\LinkerContract;
use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\UriBuilder\UriBuilder;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;

class SilkOutLinker implements LinkerContract
{
    protected string $silkPath;
    protected FilesystemAdapter $disk;
    protected string $silkSlsSpecsPath;
    protected string $linkesNtriplesFilePath;

    public function __construct(string $diskName,string $silkSlsSpecsRelativePath)
    {
        $this->silkPath=config('ldog.silk.path');
        $this->disk=Storage::disk($diskName);
        $this->silkSlsSpecsPath=$this->disk->path($silkSlsSpecsRelativePath);
        $this->linkesNtriplesFilePath=dirname($this->silkSlsSpecsPath)."accepted_links.nt";
    }

    public function performLinkage()
    {
        exec("java -DconfigFile=\"$this->silkSlsSpecsPath\" -jar \"$this->silkPath\" ",$output,$return);
        if($return > 0)
        {
            throw new \RuntimeException('error during executing silk lsl specs:'.$this->silkSlsSpecsPath);
        }

        $this->importLinks();
    }
    protected function importLinks()
    {
        $triplesFileUrl=UriBuilder::convertAbsoluteFilePathToUrl($this->linkesNtriplesFilePath);
        GS::getConnection()->loadIRIintoNamedGraph($triplesFileUrl,'http://meta.ldog.com/links');
    }

}