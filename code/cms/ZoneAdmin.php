<?php

class ZoneAdmin extends ModelAdmin{

	private static $menu_title = "Zones";
	private static $url_segment = "zones";
	private static $menu_icon = 'shop/images/icons/local-admin.png';
	private static $menu_priority = 2;

	private static $managed_models = array(
		'Zone'
	);

}
