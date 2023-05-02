<?php
namespace ShoppingFeed\Sdk\Test\Resource;

use PHPUnit\Framework\TestCase;
use ShoppingFeed\Sdk\Resource\Json;

class JsonTest extends TestCase
{
    private $json    = '{"pro1":"val1","pro2":1,"pro3":1.3}';
    private $content = [
        'pro1' => 'val1',
        'pro2' => 1,
        'pro3' => 1.3,
    ];

    public function testEncode()
    {
        $this->assertEquals(json_encode($this->content), Json::encode($this->content));
    }

    public function testDecode()
    {
        $this->assertEquals(json_decode($this->json), Json::decode($this->json));
    }

    public function testThrowDecodeException()
    {
        $this->expectException(\InvalidArgumentException::class);

        Json::decode("{qsdqsd");
    }

    public function testThrowEncodeException()
    {
        $this->expectException(\InvalidArgumentException::class);

        Json::encode(mb_convert_encoding("éàoù", 'EUC-JP', 'UTF-8'));
    }
}
