<?php

namespace Anferov\QrCode\Renderers\Image;

use Anferov\QrCode\Renderers\RendererConfig;

class ImageRendererConfig extends RendererConfig
{

    public static $borderModeHidden = 0;
    public static $borderModeOwn = 1;
    public static $borderModePixelated = 2; // border not rendered
    private static $themeContents = array(
        'markerMain' => 9,
        'markerSub' => 5,
        'pixelOn' => 1,
        'pixelOff' => 1,
        'pixelBorder' => 1
    ); // border speciffic pixel is used (default)
    private static $themeFiles = array(
        'markerMain' => 'marker_main',
        'markerSub' => 'marker_sub',
        'pixelOn' => 'pixel_on',
        'pixelOff' => 'pixel_off',
        'pixelBorder' => 'pixel_border'
    ); // border treaded as standard, white pixel
    public $markerMain = null;
    public $markerSub = null;
    public $pixelOn = null;
    public $pixelOff = null;
    public $pixelBorder = null;
    public $borderMode;
    protected $themeDir;
    function __construct($themeDirOrThemeName)
    {
        $this->detectThemeDir($themeDirOrThemeName);
        $this->ensureThemeFilesExists();
        $this->detectPixelSize();
        $this->ensureThemeImagesHaveCorrectSize();
        $this->loadThemeImages();
        $this->borderMode = ImageRendererConfig::$borderModeOwn;
    }

    private function detectThemeDir($themeDirOrThemeName)
    {
        if (!file_exists($themeDirOrThemeName)) {
            $fromPathName = realpath(__DIR__ . '/../../assets/' . $themeDirOrThemeName);
            if (!file_exists($fromPathName)) {
                throw new \Exception("Cannot find theme by name or dir: " . $themeDirOrThemeName);
            }
            $this->themeDir = $fromPathName;
        } else {
            $this->themeDir = $themeDirOrThemeName;
        }
    }

    private function ensureThemeFilesExists()
    {
        foreach (array_keys(ImageRendererConfig::$themeContents) as $themeElement) {
            if (!file_exists($this->pathForThemeImage($themeElement))) {
                throw new \Exception("Required theme file for " . $themeElement . " not found at " . $this->pathForThemeImage($themeElement));
            }
        }
    }

    private function pathForThemeImage($name)
    {
        return $this->themeDir . DIRECTORY_SEPARATOR . ImageRendererConfig::$themeFiles[$name] . '.png';
    }

    private function detectPixelSize()
    {
        $refInfo = \getimagesize($this->pathForThemeImage('pixelOn'));
        $this->pixelPerPoint = $refInfo[0];
    }

    private function ensureThemeImagesHaveCorrectSize()
    {
        foreach (ImageRendererConfig::$themeContents as $themeElement => $pixelScale) {
            $checkInfo = \getimagesize($this->pathForThemeImage($themeElement));
            if ($checkInfo[0] != $checkInfo[1]) {
                throw new \Exception("Theme element " . $themeElement . " should be square (theme at: " . $this->pathForThemeImage($themeElement) . ")");
            }

            if ($checkInfo[0] != ($pixelScale * $this->pixelPerPoint)) {
                throw new \Exception("Theme element " . $themeElement . " should be " . ($pixelScale * $this->pixelPerPoint) . " wide and tall, but got: " . $checkInfo[0] . "x" . $checkInfo[1]);
            }
        }
    }

    private function loadThemeImages()
    {
        foreach (array_keys(ImageRendererConfig::$themeContents) as $themeElement) {
            $image = \imagecreatefrompng($this->pathForThemeImage($themeElement));
            \imagealphablending($image, false); // Overwrite alpha
            \imagesavealpha($image, true);
            $this->$themeElement = $image;
        }
    }

    function __destruct()
    {
        $this->dispose();
    }

    public function dispose()
    {
        foreach (array_keys(ImageRendererConfig::$themeContents) as $themeElement) {
            if (isset($this->$themeElement) && ($this->$themeElement != null)) {
                imagedestroy($this->$themeElement);
                $this->$themeElement = null;
            }
        }
    }

    /**
     * Configure how code border will be rendered, available modes are:
     * - ImageRendererConfig::$borderModeHidden - border not rendered
     * - ImageRendererConfig::$borderModeOwn - border speciffic pixel is used (default)
     * - ImageRendererConfig::$borderModePixelated - border treaded as standard, white pixel
     * @param type $borderMode border rendering mode
     */
    public function setBorderMode($borderMode)
    {
        $this->borderMode = $borderMode;
    }
}
