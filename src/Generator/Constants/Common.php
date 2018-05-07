<?php

namespace Anferov\QrCode\Generator\Constants;

class Common
{
    public const STRUCTURE_HEADER_BITS = 20;
    public const QRCAP_WIDTH = 0;
    public const QRCAP_WORDS = 1;
    public const QRCAP_REMINDER = 2;
    public const QRCAP_EC = 3;
    public const QRSPEC_WIDTH_MAX = 177;
    public const QRSPEC_VERSION_MAX = 40;
    public const N1 = 3;
    public const N2 = 3;
    public const N3 = 40;
    public const N4 = 10;
    public const MAX_STRUCTURED_SYMBOLS = 16;
    /** maximum allowed png image width (in pixels), tune to make sure GD and PHP can handle such big images */
    public const QR_PNG_MAXIMUM_SIZE = 1024;
    /** when QR_FIND_BEST_MASK === false */
    public const QR_DEFAULT_MASK = 2;
    /** if false, checks all masks available, otherwise value tells count of masks need to be checked, mask id are got randomly */
    public const QR_FIND_FROM_RANDOM = 2;
    /** if true, estimates best mask (spec. default, but extremally slow; set to false to significant performance boost but (propably) worst quality code */
    public const QR_FIND_BEST_MASK = true;
    /** default error logs dir */
    public const QR_LOG_DIR = false;//dirname(__FILE__) . DIRECTORY_SEPARATOR)
    public const QR_IMAGE = true;
}
