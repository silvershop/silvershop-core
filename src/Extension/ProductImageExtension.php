<?php

namespace SilverShop\Extension;

use SilverStripe\Assets\Image;
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

    /**
     * @var Image
     */
    protected $owner;

    /**
     * @param bool $upscale [optional]
     * @return Image
     */
    public function getThumbnail($upscale = false)
    {
        $width = self::config()->thumbnail_width;
        $height = self::config()->thumbnail_height;

        return $this->getImageAt($width, $height, $upscale);
    }

    /**
     * @param bool $upscale [optional]
     * @return Image
     */
    public function getContentImage($upscale = false)
    {
        $width = self::config()->content_image_width;
        $height = self::config()->content_image_height;

        return $this->getImageAt($width, $height, $upscale);
    }

    /**
     * @param bool $upscale [optional]
     * @return Image
     */
    public function getLargeImage($upscale = false)
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
     *
     * @return Image
     */
    public function getImageAt($width = null, $height = null, $upscale = false)
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
    public function HasLargeImage()
    {
        $imageWidth = intval($this->owner->getWidth());
        return $imageWidth > self::config()->content_image_width;
    }
}
