<?php

namespace Anferov\QrCode\Generator;

use Anferov\QrCode\Generator\Constants\Common;
use Anferov\QrCode\Generator\Constants\Mode;

class QrInputItem
{
    public $mode;
    public $size;
    public $data;
    public $bstream;

    public function __construct($mode, $size, $data, $bstream = null)
    {
        $setData = array_slice($data, 0, $size);
        if (count($setData) < $size) {
            $setData = array_merge($setData, array_fill(0, $size - count($setData), 0));
        }
        if (!QrInput::check($mode, $size, $setData)) {
            throw new Exception('Error m:' . $mode . ',s:' . $size . ',d:' . join(',', $setData));
        }
        $this->mode = $mode;
        $this->size = $size;
        $this->data = $setData;
        $this->bstream = $bstream;
    }

    public function estimateBitStreamSizeOfEntry($version)
    {
        $bits = 0;
        if ($version == 0) {
            $version = 1;
        }
        switch ($this->mode) {
            case Mode::NUM:
                $bits = QrInput::estimateBitsModeNum($this->size);
                break;
            case Mode::AN:
                $bits = QrInput::estimateBitsModeAn($this->size);
                break;
            case Mode::BIT:
                $bits = QrInput::estimateBitsMode8($this->size);
                break;
            case Mode::KANJI:
                $bits = QrInput::estimateBitsModeKanji($this->size);
                break;
            case Mode::STRUCTURE:
                return Common::STRUCTURE_HEADER_BITS;
            default:
                return 0;
        }
        $l = QrSpec::lengthIndicator($this->mode, $version);
        $m = 1 << $l;
        $num = (int)(($this->size + $m - 1) / $m);
        $bits += $num * (4 + $l);
        return $bits;
    }

    public function encodeBitStream($version)
    {
        try {
            unset($this->bstream);
            $words = QrSpec::maximumWords($this->mode, $version);
            if ($this->size > $words) {
                $st1 = new QrInputItem($this->mode, $words, $this->data);
                $st2 = new QrInputItem($this->mode, $this->size - $words, array_slice($this->data, $words));
                $st1->encodeBitStream($version);
                $st2->encodeBitStream($version);
                $this->bstream = new QrBitStream();
                $this->bstream->append($st1->bstream);
                $this->bstream->append($st2->bstream);
                unset($st1);
                unset($st2);
            } else {
                $ret = 0;
                switch ($this->mode) {
                    case Mode::NUM:
                        $ret = $this->encodeModeNum($version);
                        break;
                    case Mode::AN:
                        $ret = $this->encodeModeAn($version);
                        break;
                    case Mode::BIT:
                        $ret = $this->encodeMode8($version);
                        break;
                    case Mode::KANJI:
                        $ret = $this->encodeModeKanji($version);
                        break;
                    case Mode::STRUCTURE:
                        $ret = $this->encodeModeStructure();
                        break;
                    default:
                        break;
                }
                if ($ret < 0) {
                    return -1;
                }
            }
            return $this->bstream->size();
        } catch (Exception $e) {
            return -1;
        }
    }

    public function encodeModeNum($version)
    {
        try {
            $words = (int)($this->size / 3);
            $bs = new QrBitStream();
            $val = 0x1;
            $bs->appendNum(4, $val);
            $bs->appendNum(QrSpec::lengthIndicator(Mode::NUM, $version), $this->size);
            for ($i = 0; $i < $words; $i++) {
                $val = (ord($this->data[$i * 3]) - ord('0')) * 100;
                $val += (ord($this->data[$i * 3 + 1]) - ord('0')) * 10;
                $val += (ord($this->data[$i * 3 + 2]) - ord('0'));
                $bs->appendNum(10, $val);
            }
            if ($this->size - $words * 3 == 1) {
                $val = ord($this->data[$words * 3]) - ord('0');
                $bs->appendNum(4, $val);
            } else {
                if ($this->size - $words * 3 == 2) {
                    $val = (ord($this->data[$words * 3]) - ord('0')) * 10;
                    $val += (ord($this->data[$words * 3 + 1]) - ord('0'));
                    $bs->appendNum(7, $val);
                }
            }
            $this->bstream = $bs;
            return 0;
        } catch (Exception $e) {
            return -1;
        }
    }

    public function encodeModeAn($version)
    {
        try {
            $words = (int)($this->size / 2);
            $bs = new QrBitStream();
            $bs->appendNum(4, 0x02);
            $bs->appendNum(QrSpec::lengthIndicator(Mode::AN, $version), $this->size);
            for ($i = 0; $i < $words; $i++) {
                $val = (int)QrInput::lookAnTable(ord($this->data[$i * 2])) * 45;
                $val += (int)QrInput::lookAnTable(ord($this->data[$i * 2 + 1]));
                $bs->appendNum(11, $val);
            }
            if ($this->size & 1) {
                $val = QrInput::lookAnTable(ord($this->data[$words * 2]));
                $bs->appendNum(6, $val);
            }
            $this->bstream = $bs;
            return 0;
        } catch (Exception $e) {
            return -1;
        }
    }

    public function encodeMode8($version)
    {
        try {
            $bs = new QrBitStream();
            $bs->appendNum(4, 0x4);
            $bs->appendNum(QrSpec::lengthIndicator(Mode::BIT, $version),
                $this->size);
            for ($i = 0; $i < $this->size; $i++) {
                $bs->appendNum(8, ord($this->data[$i]));
            }
            $this->bstream = $bs;
            return 0;
        } catch (Exception $e) {
            return -1;
        }
    }

    public function encodeModeKanji($version)
    {
        try {
            $bs = new QrBitStream();
            $bs->appendNum(4, 0x8);
            $bs->appendNum(QrSpec::lengthIndicator(Mode::KANJI, $version),(int)($this->size / 2));
            for ($i = 0; $i < $this->size; $i += 2) {
                $val = (ord($this->data[$i]) << 8) | ord($this->data[$i + 1]);
                if ($val <= 0x9ffc) {
                    $val -= 0x8140;
                } else {
                    $val -= 0xc140;
                }
                $h = ($val >> 8) * 0xc0;
                $val = ($val & 0xff) + $h;
                $bs->appendNum(13, $val);
            }
            $this->bstream = $bs;
            return 0;
        } catch (Exception $e) {
            return -1;
        }
    }

    public function encodeModeStructure()
    {
        try {
            $bs = new QrBitStream();
            $bs->appendNum(4, 0x03);
            $bs->appendNum(4, ord($this->data[1]) - 1);
            $bs->appendNum(4, ord($this->data[0]) - 1);
            $bs->appendNum(8, ord($this->data[2]));
            $this->bstream = $bs;
            return 0;
        } catch (Exception $e) {
            return -1;
        }
    }
}
