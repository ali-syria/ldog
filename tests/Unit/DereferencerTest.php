<?php


namespace AliSyria\LDOG\Tests\Unit;


use AliSyria\LDOG\Tests\TestCase;
use AliSyria\LDOG\UriDereferencer\Dereferencer;

class DereferencerTest extends TestCase
{
    public function testItReturnsAllHtmlMimeTypes()
    {
        $expected=[
            'text/html','application/xhtml+xml'
        ];
        $this->assertEqualsCanonicalizing($expected,Dereferencer::getHTMLmimeTypes());
    }

    public function testItReturnsAllRdfMimeTypes()
    {
        $expected=[
            'application/rdf+xml','text/turtle','text/n3','application/n-quads',
            'application/n-triples','application/trig','application/ld+json'
        ];
        $this->assertEqualsCanonicalizing($expected,Dereferencer::getRDFmimeTypes());
    }

    /**
     * @dataProvider rdfMimeTypesProvider
     */
    public function testIsRdfmimeType(string $mimeType)
    {
        $this->assertTrue(Dereferencer::isRDFmimeType($mimeType));
        $this->assertFalse(Dereferencer::isRDFmimeType(Dereferencer::MIME_HTML));
    }

    /**
     * @dataProvider htmlMimeTypesProvider
     */
    public function testIsHtmlmimeType(string $mimeType)
    {
        $this->assertTrue(Dereferencer::isHTMLmimeType($mimeType));
        $this->assertFalse(Dereferencer::isHTMLmimeType(Dereferencer::MIME_RDF_JSON_LD));
    }

    public function rdfMimeTypesProvider():array
    {
        $data=[];
        foreach (Dereferencer::getRDFmimeTypes() as $mimeType)
        {
            $data[$mimeType]=[$mimeType];
        }

        return $data;
    }

    public function htmlMimeTypesProvider():array
    {
        $data=[];
        foreach (Dereferencer::getHTMLmimeTypes() as $mimeType)
        {
            $data[$mimeType]=[$mimeType];
        }

        return $data;
    }
}