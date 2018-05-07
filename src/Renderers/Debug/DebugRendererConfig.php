<?php

namespace Anferov\QrCode\Renderers\Debug;

use Anferov\QrCode\Renderers\RendererConfig;

class DebugRendererConfig extends RendererConfig
{
    public $outerFrame = 4;
    public $legendSize = 150;
    public $legendVisible = true;

    function __construct()
    {
        $this->pixelPerPoint = 6;
    }
}
