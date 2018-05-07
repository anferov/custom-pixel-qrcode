<?php

namespace Khanamiryan\QrCodeTests;

use Anferov\QrCode\Reader\QrReader;
use PHPUnit\Framework\TestCase;

class QrCodeReaderTest extends TestCase
{
    public function testText1()
    {
        $image = __DIR__ . "/qrcodes/hello_world.png";
        $qrcode = new QrReader($image);
        $this->assertSame("Hello world!", $qrcode->text());
    }
}
