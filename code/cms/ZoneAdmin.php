<?php

class ZoneAdmin extends ModelAdmin{
	
	static $menu_title = "Zones";
	static $url_segment = "zones";
	
	static $managed_models = array(
		'Zone'
	);
	
}