<?php

namespace SilverShop\Extension;

use SilverStripe\Assets\Image;
use SilverStripe\Assets\Storage\AssetContainer;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\ORM\DataExtension;

/**
 * Adds some image size functions to the Image DataObject.
 *
 * @package shop
 */
class ProductImageExtension extends DataExtension
{
    use Configurable;

    protected $owner;

    /**
     * @config
     */
    private static int $thumbnail_width = 140;

    /**
     * @config
     */
    private static int $thumbnail_height = 100;

    /**
     * @config
     */
    private static int $content_image_width = 200;

    /**
     * @config
     */
    private static int $content_image_height = 0;

    /**
     * @config
     */
    private static int $large_image_width = 600;

    /**
     * @config
     */
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
     * Resizes image by width or height only if the source image is bigger than the given width/height.
     * This prevents ugly upscaling.
     *
     * @param int  $width   [optional]
     * @param int  $height  [optional]
     * @param bool $upscale [optional]
     */
    public function getImageAt($width = null, $height = null, $upscale = false): Image|AssetContainer
    {
        if (!$this->owner->exists()) {
            return $this->owner;
        }

        $realWidth = $this->owner->getWidth();
        $realHeight = $this->owner->getHeight();

        if ($width && $height) {
            return $realWidth < $width && $realHeight < $height && !$upscale
                ? $this->owner
                : $this->owner->Pad($width, $height);
        } else {
            if ($width) {
                return $realWidth < $width && !$upscale
                    ? $this->owner
                    : $this->owner->ScaleWidth($width);
            } else {
                return $realHeight < $height && !$upscale
                    ? $this->owner
                    : $this->owner->ScaleHeight($height);
            }
        }
    }

    /**
     * @return bool - is the image large enough that a "large" image makes sense?
     */
    public function HasLargeImage(): bool
    {
        $imageWidth = intval($this->owner->getWidth());
        return $imageWidth > self::config()->content_image_width;
    }
}
