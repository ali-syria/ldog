<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\Facades\GS;
use AliSyria\LDOG\Normalization\Normalizer;
use AliSyria\LDOG\Tests\TestCase;
use AliSyria\LDOG\UriBuilder\UriBuilder;

class NormalizerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        parent::setUp();
        GS::getConnection()->clearAll();
    }
    public function testHandleNormalization()
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;
        $input='health facility';

        $this->assertEquals('Health Facility',Normalizer::handle($input,$ldogPrefix.'Capitalize'));
    }
    public function testExtractNormalizationTarget()
    {
        $ldogPrefix=UriBuilder::PREFIX_LDOG;

        $this->assertEquals('capitalize',Normalizer::extractTargetMethod($ldogPrefix.'Capitalize'));
        $this->assertEquals('uppercase',Normalizer::extractTargetMethod($ldogPrefix.'Uppercase'));
        $this->assertEquals('lowercase',Normalizer::extractTargetMethod($ldogPrefix.'Lowercase'));
    }
}