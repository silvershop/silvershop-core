<?php

/**
 * Adds some image size functions to the Image DataObject.
 *
 * @package shop
 */
class Product_Image extends DataExtension
{
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
     * @param int  $width [optional]
     * @param int  $height [optional]
     * @param bool $upscale [optional]
     *
     * @return Image
     */
    public function getImageAt($width = null, $height = null, $upscale = false)
    {
        if (!$this->owner->exists()) {
            return $this->owner;
        }

        $dim = explode('x', $this->owner->getDimensions());

        if ($width && $height) {
            return $dim[0] < $width && $dim[1] < $height && !$upscale
                ? $this->owner
                : $this->owner->SetSize($width, $height);
        } else {
            if ($width) {
                return $dim[0] < $width && !$upscale
                    ? $this->owner
                    : $this->owner->SetWidth($width);
            } else {
                return $dim[1] < $width && !$upscale
                    ? $this->owner
                    : $this->owner->SetHeight($height);
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

    public static function config()
    {
        return new Config_ForClass("Product_Image");
    }
}
