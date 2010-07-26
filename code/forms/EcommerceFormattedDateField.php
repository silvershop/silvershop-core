<?php

class EcommerceFormattedDateField extends DateField {

	protected $config = array(
		'showcalendar' => true,
		'jslocale' => null,
		'dmyfields' => false,
		'dmyseparator' => '&nbsp;<span class="separator">-</span>&nbsp;',
		'dateformat' => 'yyyy-MM-dd',
		'datavalueformat' => 'yyyy-MM-dd',
		'min' => null,
		'max' => null
	);

}
