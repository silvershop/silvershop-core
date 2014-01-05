<?php

/**
 * Adds some image size functions to the Image DataObject.
 */
class Product_Image extends DataExtension {

	//default image sizes
	protected static $thumbnail_width = 140;
	protected static $thumbnail_height = 100;
	protected static $content_image_width = 200;
	protected static $large_image_width = 600;

	static function set_thumbnail_size($width = 140, $height = 100){
		self::$thumbnail_width = $width;
		self::$thumbnail_height = $height;
	}

	static function set_content_image_width($width = 200){
		self::$content_image_width = $width;
	}

	static function set_large_image_width($width = 600){
		self::$large_image_width = $width;
	}

	public function getThumbnail() {
		return $this->owner->SetSize(self::$thumbnail_width,self::$thumbnail_height);
	}

	public function getContentImage() {
		return $this->owner->SetWidth(self::$content_image_width);
	}

	public function getLargeImage() {
		return $this->owner->SetWidth(self::$large_image_width);
	}

}