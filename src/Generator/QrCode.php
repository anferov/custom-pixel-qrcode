<?php

namespace Anferov\QrCode\Generator;

use Anferov\QrCode\Generator\Constants\Common;
use Anferov\QrCode\Generator\Constants\CorrectionLevel;
use Anferov\QrCode\Generator\Constants\Mode;

class QrCode
{

    public $version;
    public $width;
    public $data;

    //----------------------------------------------------------------------

    public static function png(
        $text,
        $outfile = false,
        $level = CorrectionLevel::LOW,
        $size = 3,
        $margin = 4,
        $saveandprint = false
    ) {
        $enc = QrEncode::factory($level, $size, $margin);
        return $enc->encodePNG($text, $outfile, $saveandprint = false);
    }

    //----------------------------------------------------------------------

    public static function text($text, $outfile = false, $level = CorrectionLevel::LOW, $size = 3, $margin = 4)
    {
        $enc = QrEncode::factory($level, $size, $margin);
        return $enc->encode($text, $outfile);
    }

    //----------------------------------------------------------------------

    public static function raw($text, $outfile = false, $level = CorrectionLevel::LOW, $size = 3, $margin = 4)
    {
        $enc = QrEncode::factory($level, $size, $margin);
        return $enc->encodeRAW($text, $outfile);
    }

    //----------------------------------------------------------------------

    public function encodeString8bit($string, $version, $level)
    {
        if (string == null) {
            throw new Exception('empty string!');
            return null;
        }

        $input = new QrInput($version, $level);
        if ($input == null) {
            return null;
        }

        $ret = $input->append($input, Mode::BIT, strlen($string), str_split($string));
        if ($ret < 0) {
            unset($input);
            return null;
        }
        return $this->encodeInput($input);
    }

    //----------------------------------------------------------------------

    public function encodeInput(QrInput $input)
    {
        return $this->encodeMask($input, -1);
    }

    //----------------------------------------------------------------------

    public function encodeMask(QrInput $input, $mask)
    {
        if ($input->getVersion() < 0 || $input->getVersion() > Common::QRSPEC_VERSION_MAX) {
            throw new Exception('wrong version');
        }
        if ($input->getErrorCorrectionLevel() > CorrectionLevel::HIGH) {
            throw new Exception('wrong level');
        }

        $raw = new QrRawCode($input);

        QrTools::markTime('after_raw');

        $version = $raw->version;
        $width = QrSpec::getWidth($version);
        $frame = QrSpec::newFrame($version);

        $filler = new FrameFiller($width, $frame);
        if (is_null($filler)) {
            return null;
        }

        // inteleaved data and ecc codes
        for ($i = 0; $i < $raw->dataLength + $raw->eccLength; $i++) {
            $code = $raw->getCode();
            $bit = 0x80;
            for ($j = 0; $j < 8; $j++) {
                $addr = $filler->next();
                $filler->setFrameAt($addr, 0x02 | (($bit & $code) != 0));
                $bit = $bit >> 1;
            }
        }

        QrTools::markTime('after_filler');

        unset($raw);

        // remainder bits
        $j = QrSpec::getRemainder($version);
        for ($i = 0; $i < $j; $i++) {
            $addr = $filler->next();
            $filler->setFrameAt($addr, 0x02);
        }

        $frame = $filler->frame;
        unset($filler);


        // masking
        $maskObj = new QrMask();
        if ($mask < 0) {

            if (Common::QR_FIND_BEST_MASK) {
                $masked = $maskObj->mask($width, $frame, $input->getErrorCorrectionLevel());
            } else {
                $masked = $maskObj->makeMask($width, $frame, (intval(Common::QR_DEFAULT_MASK) % 8),
                    $input->getErrorCorrectionLevel());
            }
        } else {
            $masked = $maskObj->makeMask($width, $frame, $mask, $input->getErrorCorrectionLevel());
        }

        if ($masked == null) {
            return null;
        }

        QrTools::markTime('after_mask');

        $this->version = $version;
        $this->width = $width;
        $this->data = $masked;

        return $this;
    }

    //----------------------------------------------------------------------

    public function encodeString($string, $version, $level, $hint, $casesensitive)
    {

        if ($hint != Mode::BIT && $hint != Mode::KANJI) {
            throw new Exception('bad hint');
            return null;
        }

        $input = new QrInput($version, $level);
        if ($input == null) {
            return null;
        }

        $ret = QrSplit::splitStringToQRinput($string, $input, $hint, $casesensitive);
        if ($ret < 0) {
            return null;
        }

        return $this->encodeInput($input);
    }
}
