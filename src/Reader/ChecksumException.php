<?php

namespace Anferov\QrCode\Reader;

/**
 * Thrown when a barcode was successfully detected and decoded, but
 * was not returned because its checksum feature failed.
 *
 * @author Sean Owen
 */
final class ChecksumException extends ReaderException
{
    private static $instance;

    public static function getChecksumInstance($cause = null)
    {
        if (self::$isStackTrace) {
            return new ChecksumException($cause);
        } else {
            if (!self::$instance) {
                self::$instance = new ChecksumException($cause);
            }

            return self::$instance;
        }
    }
}
