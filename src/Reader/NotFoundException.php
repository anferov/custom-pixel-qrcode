<?php

namespace Anferov\QrCode\Reader;

/**
 * Thrown when a barcode was not found in the image. It might have been
 * partially detected but could not be confirmed.
 *
 * @author Sean Owen
 */
final class NotFoundException extends ReaderException
{
    private static $instance;

    public static function getNotFoundInstance()
    {
        if (!self::$instance) {
            self::$instance = new NotFoundException();
        }

        return self::$instance;
    }
}
