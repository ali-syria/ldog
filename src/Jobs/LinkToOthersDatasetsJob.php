<?php


namespace AliSyria\LDOG\Jobs;

use AliSyria\LDOG\OuterLinkage\SilkOutLinker;
use AliSyria\LDOG\PublishingPipeline\PublishingPipeline;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class LinkToOthersDatasetsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $diskName;
    public string $silkSlsSpecsRelativePath;

    public $timeout = 3600;

    public function __construct(string $diskName,string $silkSlsSpecsRelativePath)
    {
        $this->diskName=$diskName;
        $this->silkSlsSpecsRelativePath=$silkSlsSpecsRelativePath;
    }

    public function handle()
    {
        (new SilkOutLinker($this->diskName,$this->silkSlsSpecsRelativePath))
            ->performLinkage();
    }
}