<?php

namespace Anferov\QrCode\Generator;

use Anferov\QrCode\Generator\Constants\Mode;

class QrSplit
{
    public $dataStr = '';
    public $input;
    public $modeHint;

    public function __construct($dataStr, $input, $modeHint)
    {
        $this->dataStr = $dataStr;
        $this->input = $input;
        $this->modeHint = $modeHint;
    }

    public static function splitStringToQrInput($string, QrInput $input, $modeHint, $casesensitive = true)
    {
        if (is_null($string) || $string == '\0' || $string == '') {
            throw new Exception('empty string!!!');
        }
        $split = new QrSplit($string, $input, $modeHint);
        if (!$casesensitive) {
            $split->toUpper();
        }
        return $split->splitString();
    }

    public function toUpper()
    {
        $stringLen = strlen($this->dataStr);
        $p = 0;
        while ($p < $stringLen) {
            $mode = self::identifyMode(substr($this->dataStr, $p), $this->modeHint);
            if ($mode == Mode::KANJI) {
                $p += 2;
            } else {
                if (ord($this->dataStr[$p]) >= ord('a') && ord($this->dataStr[$p]) <= ord('z')) {
                    $this->dataStr[$p] = chr(ord($this->dataStr[$p]) - 32);
                }
                $p++;
            }
        }
        return $this->dataStr;
    }

    public function identifyMode($pos)
    {
        if ($pos >= strlen($this->dataStr)) {
            return Mode::NUL;
        }
        $c = $this->dataStr[$pos];
        if (self::isdigitat($this->dataStr, $pos)) {
            return Mode::NUM;
        } else {
            if (self::isalnumat($this->dataStr, $pos)) {
                return Mode::AN;
            } else {
                if ($this->modeHint == Mode::KANJI) {

                    if ($pos + 1 < strlen($this->dataStr)) {
                        $d = $this->dataStr[$pos + 1];
                        $word = (ord($c) << 8) | ord($d);
                        if (($word >= 0x8140 && $word <= 0x9ffc) || ($word >= 0xe040 && $word <= 0xebbf)) {
                            return Mode::KANJI;
                        }
                    }
                }
            }
        }
        return Mode::BIT;
    }

    public static function isdigitat($str, $pos)
    {
        if ($pos >= strlen($str)) {
            return false;
        }
        return ((ord($str[$pos]) >= ord('0')) && (ord($str[$pos]) <= ord('9')));
    }

    public static function isalnumat($str, $pos)
    {
        if ($pos >= strlen($str)) {
            return false;
        }
        return (QrInput::lookAnTable(ord($str[$pos])) >= 0);
    }

    public function splitString()
    {
        while (strlen($this->dataStr) > 0) {
            if ($this->dataStr == '') {
                return 0;
            }
            $mode = $this->identifyMode(0);
            switch ($mode) {
                case Mode::NUM:
                    $length = $this->eatNum();
                    break;
                case Mode::AN:
                    $length = $this->eatAn();
                    break;
                case Mode::KANJI:
                    if ($hint == Mode::KANJI) {
                        $length = $this->eatKanji();
                    } else {
                        $length = $this->eat8();
                    }
                    break;
                default:
                    $length = $this->eat8();
                    break;
            }
            if ($length == 0) {
                return 0;
            }
            if ($length < 0) {
                return -1;
            }
            $this->dataStr = substr($this->dataStr, $length);
        }
    }

    public function eatNum()
    {
        $ln = QrSpec::lengthIndicator(Mode::NUM, $this->input->getVersion());
        $p = 0;
        while (self::isdigitat($this->dataStr, $p)) {
            $p++;
        }
        $run = $p;
        $mode = $this->identifyMode($p);
        if ($mode == Mode::BIT) {
            $dif = QrInput::estimateBitsModeNum($run) + 4 + $ln
                + QrInput::estimateBitsMode8(1)         // + 4 + l8
                - QrInput::estimateBitsMode8($run + 1); // - 4 - l8
            if ($dif > 0) {
                return $this->eat8();
            }
        }
        if ($mode == Mode::AN) {
            $dif = QrInput::estimateBitsModeNum($run) + 4 + $ln
                + QrInput::estimateBitsModeAn(1)        // + 4 + la
                - QrInput::estimateBitsModeAn($run + 1);// - 4 - la
            if ($dif > 0) {
                return $this->eatAn();
            }
        }
        $ret = $this->input->append(Mode::NUM, $run, str_split($this->dataStr));
        if ($ret < 0) {
            return -1;
        }
        return $run;
    }

    public function eat8()
    {
        $la = QrSpec::lengthIndicator(Mode::AN, $this->input->getVersion());
        $ln = QrSpec::lengthIndicator(Mode::NUM, $this->input->getVersion());
        $p = 1;
        $dataStrLen = strlen($this->dataStr);
        while ($p < $dataStrLen) {
            $mode = $this->identifyMode($p);
            if ($mode == Mode::KANJI) {
                break;
            }
            if ($mode == Mode::NUM) {
                $q = $p;
                while (self::isdigitat($this->dataStr, $q)) {
                    $q++;
                }
                $dif = QrInput::estimateBitsMode8($p) // + 4 + l8
                    + QrInput::estimateBitsModeNum($q - $p) + 4 + $ln
                    - QrInput::estimateBitsMode8($q); // - 4 - l8
                if ($dif < 0) {
                    break;
                } else {
                    $p = $q;
                }
            } else {
                if ($mode == Mode::AN) {
                    $q = $p;
                    while (self::isalnumat($this->dataStr, $q)) {
                        $q++;
                    }
                    $dif = QrInput::estimateBitsMode8($p)  // + 4 + l8
                        + QrInput::estimateBitsModeAn($q - $p) + 4 + $la
                        - QrInput::estimateBitsMode8($q); // - 4 - l8
                    if ($dif < 0) {
                        break;
                    } else {
                        $p = $q;
                    }
                } else {
                    $p++;
                }
            }
        }
        $run = $p;
        $ret = $this->input->append(Mode::BIT, $run, str_split($this->dataStr));
        if ($ret < 0) {
            return -1;
        }
        return $run;
    }

    public function eatAn()
    {
        $la = QrSpec::lengthIndicator(Mode::AN, $this->input->getVersion());
        $ln = QrSpec::lengthIndicator(Mode::NUM, $this->input->getVersion());
        $p = 0;
        while (self::isalnumat($this->dataStr, $p)) {
            if (self::isdigitat($this->dataStr, $p)) {
                $q = $p;
                while (self::isdigitat($this->dataStr, $q)) {
                    $q++;
                }
                $dif = QrInput::estimateBitsModeAn($p) // + 4 + la
                    + QrInput::estimateBitsModeNum($q - $p) + 4 + $ln
                    - QrInput::estimateBitsModeAn($q); // - 4 - la
                if ($dif < 0) {
                    break;
                } else {
                    $p = $q;
                }
            } else {
                $p++;
            }
        }
        $run = $p;
        if (!self::isalnumat($this->dataStr, $p)) {
            $dif = QrInput::estimateBitsModeAn($run) + 4 + $la
                + QrInput::estimateBitsMode8(1) // + 4 + l8
                - QrInput::estimateBitsMode8($run + 1); // - 4 - l8
            if ($dif > 0) {
                return $this->eat8();
            }
        }
        $ret = $this->input->append(Mode::AN, $run, str_split($this->dataStr));
        if ($ret < 0) {
            return -1;
        }
        return $run;
    }

    public function eatKanji()
    {
        $p = 0;
        while ($this->identifyMode($p) == Mode::KANJI) {
            $p += 2;
        }
        $ret = $this->input->append(Mode::KANJI, $p, str_split($this->dataStr));
        if ($ret < 0) {
            return -1;
        }
        return $run;
    }
}
