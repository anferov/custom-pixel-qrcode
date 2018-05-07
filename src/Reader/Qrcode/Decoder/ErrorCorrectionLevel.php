<?php

namespace Anferov\QrCode\Reader\Qrcode\Decoder;

/**
 * <p>See ISO 18004:2006, 6.5.1. This enum encapsulates the four error correction levels
 * defined by the QR code standard.</p>
 *
 * @author Sean Owen
 */
class ErrorCorrectionLevel
{
    private static $FOR_BITS;
    private $bits;
    private $ordinal;

    public function __construct($bits, $ordinal = 0)
    {
        $this->bits = $bits;
        $this->ordinal = $ordinal;
    }

    public static function Init()
    {
        self::$FOR_BITS = [


            new ErrorCorrectionLevel(0x00, 1), //M
            new ErrorCorrectionLevel(0x01, 0), //L
            new ErrorCorrectionLevel(0x02, 3), //H
            new ErrorCorrectionLevel(0x03, 2), //Q

        ];
    }
    /** L = ~7% correction */
    //  self::$L = new ErrorCorrectionLevel(0x01);
    /** M = ~15% correction */
    //self::$M = new ErrorCorrectionLevel(0x00);
    /** Q = ~25% correction */
    //self::$Q = new ErrorCorrectionLevel(0x03);
    /** H = ~30% correction */
    //self::$H = new ErrorCorrectionLevel(0x02);
    /**
     * @param bits int containing the two bits encoding a QR Code's error correction level
     *
     * @return ErrorCorrectionLevel representing the encoded error correction level
     */
    public static function forBits($bits)
    {
        if ($bits < 0 || $bits >= count(self::$FOR_BITS)) {
            throw new \InvalidArgumentException();
        }
        $level = self::$FOR_BITS[$bits];

        // $lev = self::$$bit;
        return $level;
    }


    public function getBits()
    {
        return $this->bits;
    }

    public function toString()
    {
        return $this->bits;
    }

    public function getOrdinal()
    {
        return $this->ordinal;
    }
}

ErrorCorrectionLevel::Init();
