<?php

/**
 * Adds some image size functions to the Image DataObject.
 */
class Product_Image extends DataExtension {
	
	private static $thumbnail_width = 140;
	private static $thumbnail_height = 100;
	protected static $content_image_width = 200;
	protected static $large_image_width = 600;

	public function getThumbnail() {
		return $this->owner->SetSize(
			self::config()->thumbnail_width,
			self::config()->thumbnail_height
		);
	}

	public function getContentImage() {
		return $this->owner->SetWidth(
			self::config()->content_image_width
		);
	}

	public function getLargeImage() {
		return $this->owner->SetWidth(
			self::config()->large_image_width
		);
	}

	public static function config(){
		return new Config_ForClass("Product_Image");
	}

}