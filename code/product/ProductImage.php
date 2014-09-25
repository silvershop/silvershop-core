<?php

/**
 * Adds some image size functions to the Image DataObject.
 *
 * @package shop
 */
class Product_Image extends DataExtension {


	public function getThumbnail() {
		$width = self::config()->thumbnail_width;
		$height = self::config()->thumbnail_height;

		return $this->getImageAt($width, $height);
	}

	public function getContentImage() {
		$width = self::config()->content_image_width;
		$height = self::config()->content_image_height;

		return $this->getImageAt($width, $height);
	}

	public function getLargeImage() {
		$width = self::config()->large_image_width;
		$height = self::config()->large_image_height;

		return $this->getImageAt($width, $height);
	}

	public function getImageAt($width = null, $height = null) {
		if($width && $height) {
			return $this->owner->SetSize($width, $height);
		} else if($width) {
			return $this->owner->SetWidth($width);
		} else {
			return $this->owner->SetHeight($height);
		}
	}

	public static function config(){
		return new Config_ForClass("Product_Image");
	}

}
