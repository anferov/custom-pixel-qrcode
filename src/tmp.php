<?php

/*
 * @copyright Copyright (c) ADV/web-engineering co.
 */

namespace OsagoBundle\Service;

use Anferov\QrCode\Generator\Constants\CorrectionLevel;
use DeltaLab\CustomPixelQRCode\CodeFrame;
use DeltaLab\CustomPixelQRCode\Renderers\Image\ImageCodeRenderer;
use DeltaLab\CustomPixelQRCode\Renderers\Image\ImageRendererConfig;

class QrCodeService
{
    const ECCLEVEL_L = 0;
    const ECCLEVEL_M = 1;
    const ECCLEVEL_Q = 2;
    const ECCLEVEL_H = 4;

    const BORDERED_STYLE = 'bordered-24';
    const FULL_STYLE = 'full-24';
    const ROUNDED_STYLE = 'rounded-24';
    const CIRCLE_STYLE = 'small-circle-24';
    const LESS_TRANSPARENT_STYLE = 'transparent-less-24';
    const MORE_TRANSPARENT_STYLE = 'transparent-more-24';

    private $pathToStoreFiles;

    public function setPathToStoreFiles(string $pathToStoreFiles)
    {
        $this->pathToStoreFiles = $pathToStoreFiles;
    }

    public function renderQrCode(
        string $text,
        int $eccLevel = self::ECCLEVEL_L,
        string $style = self::FULL_STYLE
    ): string {
        $fileName = $this->generateFileName($text);
        $imageRenderer = new ImageCodeRenderer(
            new CodeFrame($this->normalizeText($text), CorrectionLevel::LOW),
            new ImageRendererConfig($style)
        );
        $imageRenderer->renderToFile($this->pathToStoreFiles . '/' . $fileName);
        return $fileName;
    }

    private function generateFileName(string $text): string
    {
        return md5($text) . '.png';
    }

    private function normalizeText(string $text): string
    {
        $text = array_reduce(preg_split('//u', $text, null, PREG_SPLIT_NO_EMPTY), function ($carry, $item) {
            static $flag = null;
            static $id = 0;
            $rus = preg_match('/[а-яёА-ЯЁ ]/iu', $item);
            $flag = is_null($flag) ? $rus : $flag;
            if ($flag != $rus) {
                $id++;
            }
            $flag = $rus;
            if (isset($carry[$id])) {
                $carry[$id] .= $item;
            } else {
                $carry[$id] = $item;
            }
            return $carry;
        }, []);
        for ($i = 0; $i < count($text); $i++) {
            if (preg_match('/[а-яёА-ЯЁ]/iu', mb_substr($text[$i], 0, 1))) {
                $spiceCount = strlen($text[$i]) - substr_count($text[$i], ' ');
                $s = str_pad($text[$i], $spiceCount, ' ', STR_PAD_RIGHT);
                $text[$i] = $s;
            }
        }
        return implode('', $text);
    }
}
