<?php

declare(strict_types=1);

namespace SilverShop\Extension;

use SilverStripe\Assets\Image;
use SilverStripe\Assets\Storage\AssetContainer;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Extension;

/**
 * Adds some image size functions to the Image DataObject.
 *
 * @package shop
 * @extends Extension<(Image & static)>
 */
class ProductImageExtension extends Extension
{
    use Configurable;

    protected $owner;

    private static int $thumbnail_width = 140;

    private static int $thumbnail_height = 100;

    private static int $content_image_width = 200;

    private static int $content_image_height = 0;

    private static int $large_image_width = 600;

    private static int $large_image_height = 0;

    /**
     * @param bool $upscale [optional]
     */
    public function getThumbnail($upscale = false): Image|AssetContainer
    {
        $width = self::config()->thumbnail_width;
        $height = self::config()->thumbnail_height;

        return $this->getImageAt($width, $height, $upscale);
    }

    /**
     * @param bool $upscale [optional]
     */
    public function getContentImage($upscale = false): Image|AssetContainer
    {
        $width = self::config()->content_image_width;
        $height = self::config()->content_image_height;

        return $this->getImageAt($width, $height, $upscale);
    }

    /**
     * @param bool $upscale [optional]
     */
    public function getLargeImage($upscale = false): Image|AssetContainer
    {
        $width = self::config()->large_image_width;
        $height = self::config()->large_image_height;

        return $this->getImageAt($width, $height, $upscale);
    }

    /**
     * Resizes image to the given width and/or height.
     * When upscale is false (default), uses FitMax/ScaleMaxWidth/ScaleMaxHeight to prevent upscaling
     * while still processing the image through the resampling pipeline.
     *
     * @param int  $width   [optional]
     * @param int  $height  [optional]
     * @param bool $upscale [optional]
     */
    public function getImageAt($width = null, $height = null, $upscale = false): Image|AssetContainer
    {
        if (!$this->getOwner()->exists()) {
            return $this->getOwner();
        }

        if ($width && $height) {
            return $upscale
                ? $this->getOwner()->Pad($width, $height)
                : $this->getOwner()->FitMax($width, $height);
        }

        if ($width) {
            return $upscale
                ? $this->getOwner()->ScaleWidth($width)
                : $this->getOwner()->ScaleMaxWidth($width);
        }

        return $upscale
            ? $this->getOwner()->ScaleHeight($height)
            : $this->getOwner()->ScaleMaxHeight($height);
    }

    /**
     * @return bool - is the image large enough that a "large" image makes sense?
     */
    public function HasLargeImage(): bool
    {
        $imageWidth = intval($this->getOwner()->getWidth());
        return $imageWidth > self::config()->content_image_width;
    }
}
