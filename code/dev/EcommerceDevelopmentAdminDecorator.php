<?php

class EcommerceDevelopmentAdminDecorator extends Extension{

	function ecommerce($request) {
		if(Director::is_cli()) {
			$da = Object::create('EcommerceDatabaseAdmin');
			return $da->handleRequest($request);
		} else {
			$renderer = Object::create('DebugView');
			$renderer->writeHeader();
			$renderer->writeInfo("Ecommerce Development Tools", Director::absoluteBaseURL());
			echo "<div style=\"margin: 0 2em\">";

			$da = Object::create('EcommerceDatabaseAdmin');
			return $da->handleRequest($request);

			echo "</div>";
			$renderer->writeFooter();

		}
	}

}