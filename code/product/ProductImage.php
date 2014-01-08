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

	public function getThumbnail() {
		return $this->owner->SetSize(
			Config::get('Product_Image','thumbnail_width'),
			Config::get('Product_Image','thumbnail_height')
		);
	}

	public function getContentImage() {
		return $this->owner->SetWidth(
			Config::get('Product_Image','content_image_width')
		);
	}

	public function getLargeImage() {
		return $this->owner->SetWidth(
			Config::get('Product_Image','large_image_width')
		);
	}

}