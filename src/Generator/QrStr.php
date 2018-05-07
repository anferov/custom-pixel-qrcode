<?php

namespace Anferov\QrCode\Generator;

class QrStr
{
    public static function set(&$srctab, $x, $y, $repl, $replLen = false)
    {
        $srctab[$y] = substr_replace($srctab[$y], ($replLen !== false) ? substr($repl, 0, $replLen) : $repl, $x,
            ($replLen !== false) ? $replLen : strlen($repl));
    }
}
