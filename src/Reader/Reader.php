<?php

namespace Anferov\QrCode\Reader;

interface Reader
{
    public function decode(BinaryBitmap $image);

    public function reset();
}
