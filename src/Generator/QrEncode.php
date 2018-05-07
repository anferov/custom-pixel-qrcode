<?php

namespace Anferov\QrCode\Generator;

use Anferov\QrCode\Generator\Constants\Common;
use Anferov\QrCode\Generator\Constants\CorrectionLevel;
use Anferov\QrCode\Generator\Constants\Mode;

class QrEncode
{
    public $casesensitive = true;
    public $eightbit = false;
    public $version = 0;
    public $size = 3;
    public $margin = 4;
    public $structured = 0; // not supported yet
    public $level = CorrectionLevel::LOW;
    public $hint = Mode::BIT;

    public static function factory($level = CorrectionLevel::LOW, $size = 3, $margin = 4) {
        $enc = new QrEncode();
        $enc->size = $size;
        $enc->margin = $margin;
        switch ($level . '') {
            case '0':
            case '1':
            case '2':
            case '3':
                $enc->level = $level;
                break;
            case 'l':
            case 'L':
                $enc->level = CorrectionLevel::LOW;
                break;
            case 'm':
            case 'M':
                $enc->level = CorrectionLevel::MANUAL;
                break;
            case 'q':
            case 'Q':
                $enc->level = CorrectionLevel::QUALITY;
                break;
            case 'h':
            case 'H':
                $enc->level = CorrectionLevel::HIGH;
                break;
        }
        return $enc;
    }

    public function encodeRAW($intext, $outfile = false)
    {
        $code = new QrCode();
        if ($this->eightbit) {
            $code->encodeString8bit($intext, $this->version, $this->level);
        } else {
            $code->encodeString($intext, $this->version, $this->level, $this->hint, $this->casesensitive);
        }
        return $code->data;
    }

    public function encodePNG($intext, $outfile = false, $saveandprint = false)
    {
        try {
            ob_start();
            $tab = $this->encode($intext);
            $err = ob_get_contents();
            ob_end_clean();

            if ($err != '') {
                QrTools::log($outfile, $err);
            }
            $maxSize = (int)(Common::QR_PNG_MAXIMUM_SIZE / (count($tab) + 2 * $this->margin));
            QrImage::png($tab, $outfile, min(max(1, $this->size), $maxSize), $this->margin, $saveandprint);
        } catch (Exception $e) {
            QrTools::log($outfile, $e->getMessage());
        }
    }

    public function encode($intext, $outfile = false)
    {
        $code = new QrCode();
        if ($this->eightbit) {
            $code->encodeString8bit($intext, $this->version, $this->level);
        } else {
            $code->encodeString($intext, $this->version, $this->level, $this->hint, $this->casesensitive);
        }
        QrTools::markTime('after_encode');
        if ($outfile !== false) {
            file_put_contents($outfile, join("\n", QrTools::binarize($code->data)));
        } else {
            return QrTools::binarize($code->data);
        }
    }
}
