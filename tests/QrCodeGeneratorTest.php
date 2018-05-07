<?php

namespace Khanamiryan\QrCodeTests;

use Anferov\QrCode\Generator\QrCode;
use Anferov\QrCode\Reader\QrReader;
use PHPUnit\Framework\TestCase;

class QrCodeGeneratorTest extends TestCase
{
    private const IMAGE_DIR = '/qrcodes/';

    /**
     * @dataProvider textProvider
     */
    public function testGeneration(string $text)
    {
        $image = dirname(__FILE__) . self::IMAGE_DIR . 'test.png';
        QrCode::png($text, $image, 'L', 4, 2);
        $qrcode = new QrReader($image);
        $this->assertSame($text, $qrcode->text());
    }

    public function textProvider()
    {
        return [
            ['Hello world!'],
            ['Русский текст'],
            ['https://dkbm-web.autoins.ru/dkbm-web-1.0/qr.htm?id=ЕЕЕ0123456789'],
        ];
    }
}
