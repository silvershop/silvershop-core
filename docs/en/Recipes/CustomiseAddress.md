# Customise Address Fields and Display

You may want to store and display extra details for an Address

## Address Model DB Fields and form fields with a Decorator

In [mysite]/code/ExtendedAddress.php

	:::php
	<?php
	class ExtendedCustomer extends DataObjectDecorator{
		
		/**
		* Add Company, Suburb, and Fax to Address data model
		*/
		function extraStatics(){
			return array(
				'db' => array(
					'Company' => 'Varchar',
					'Suburb' => 'Varchar',
					'Fax' => 'Varchar'
				)
			);
		}
		
		function updateFormFields(FieldList $fields,$nameprefix = ""){
			$fields->insertBefore(new TextField($nameprefix.'Suburb',"Suburb"),$nameprefix.'City');
			$fields->insertAfter(new TextField($nameprefix.'Fax',"Fax"),$nameprefix.'Phone');
			$fields->insertFirst(new TextField($nameprefix.'Company',"Company"));
		}
		
		
	}

To your _config.php file, add:

	:::php
	Object::add_extension('Address','ExtendedAddress');

## Address tempalte

Make a copy of shop/templates/Includes/Address.ss, and put this copy into your
templates/Includes/ folder, either in mysite or a theme folder.

Add new data fields, as desired:

	<% if Company %>$Company<br/><% end_if %>
	<% if Suburb %>$Suburb<br/><% end_if %>
	<% if Fax %>$Fax<% end_if %>

	