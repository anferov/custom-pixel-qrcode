<?php

namespace Anferov\QrCode\Reader;

/**
 * The general exception class throw when something goes wrong during decoding of a barcode.
 * This includes, but is not limited to, failing checksums / error correction algorithms, being
 * unable to locate finder timing patterns, and so on.
 *
 * @author Sean Owen
 */
abstract class ReaderException extends \Exception
{

// disable stack traces when not running inside test units
    //protected static  $isStackTrace = System.getProperty("surefire.test.class.path") != null;
    protected static $isStackTrace = false;

    function ReaderException($cause = null)
    {
        if ($cause) {
            parent::__construct($cause);
        }
    }


// Prevent stack traces from being taken
// srowen says: huh, my IDE is saying this is not an override. native methods can't be overridden?
// This, at least, does not hurt. Because we use a singleton pattern here, it doesn't matter anyhow.
//@Override
    public final function fillInStackTrace()
    {
        return null;
    }
}
