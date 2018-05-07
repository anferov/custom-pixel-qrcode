<?php

namespace Anferov\QrCode\Renderers;

use Anferov\QrCode\CodeFrame;

abstract class CodeRenderer
{
    public $config = null;
    public $rendered = null;
    protected $frame = false;

    public function __construct(CodeFrame $preProcessedFrame = null, RendererConfig $config = null)
    {
        if ($preProcessedFrame !== null) {
            $this->setFrame($preProcessedFrame);
        }
        if ($config !== null) {
            $this->setConfig($config);
        }
    }

    public function setFrame(CodeFrame $preProcessedFrame)
    {
        $this->frame = $preProcessedFrame;
    }

    public function setConfig(RendererConfig $config)
    {
        $this->config = $config;
    }

    function __destruct()
    {
        $this->dispose();
    }

    public function dispose()
    {
        if (isset($this->rendered) && ($this->rendered != null)) {
            if (is_resource($this->rendered)) {
                \imagedestroy($this->rendered);
                $this->rendered = null;
            }
        }
    }

    /**
     * Get render size, in pixels
     * @return int image size
     */
    public function getDim()
    {
        if (isset($this->config->pixelPerPoint)) {
            return $this->frame->size * $this->config->pixelPerPoint;
        }

        return $this->frame->size;
    }

    /**
     * Get code size, in logical pixels
     * @return int logical code size
     */
    public function getSize()
    {
        return $this->frame->size;
    }

    /**
     * Get single logical pixel size.
     * It shows scale factor - size of image representing single logical pixel.
     * @return int pixel size
     */
    public function getPixelSize()
    {
        if (isset($this->config->pixelPerPoint)) {
            return $this->config->pixelPerPoint;
        }

        return 1;
    }

    public function renderToFile($outputFileName)
    {
        $image = $this->render();
        \imagepng($image, $outputFileName);
        \imagedestroy($image);
    }

    public abstract function render();

    public function renderIfNeeded()
    {
        if ($this->rendered == null) {
            $this->renderInMemory();
        }
    }

    public function renderInMemory()
    {
        $this->dispose();
        $this->rendered = $this->render();
    }
}
