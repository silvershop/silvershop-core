jQuery(document).ready(
	function() {
		jQuery('input.ajaxQuantityField').each(
			function() {
				jQuery(this).removeAttr('disabled');
				jQuery(this).change(
					function() {
						var name = jQuery(this).attr('name')+ '_SetQuantityLink';
						var setQuantityLink = jQuery('[name=' + name + ']');
						if(jQuery(setQuantityLink).length > 0) {
							setQuantityLink = jQuery(setQuantityLink).get(0);
							if(! this.value) this.value = 0;
							else this.value = this.value.replace(/[^0-9]+/g, '');
							var url = jQuery('base').attr('href') + setQuantityLink.value + '?quantity=' + this.value;
							jQuery.getJSON(url, null, setChanges);
						}
					}
				);
			}
		);
		jQuery('select.ajaxCountryField').each(
			function() {
				jQuery(this).removeAttr('disabled');
				jQuery(this).change(
					function() {
						var id = '#' + jQuery(this).attr('id') + '_SetCountryLink';
						var setCountryLink = jQuery(id);
						if(jQuery(setCountryLink).length > 0) {
							setCountryLink = jQuery(setCountryLink).get(0);
							var url = jQuery('base').attr('href') + setCountryLink.value + '/' + this.value;
							jQuery.getJSON(url, null, setChanges);
						}
					}
				);
			}
		);
	}
);

function setChanges(changes) {
	for(var i in changes) {
		var change = changes[i];
		if(typeof(change.parameter) != 'undefined' && typeof(change.value) != 'undefined') {
			var parameter = change.parameter;
			var value = escapeHTML(change.value);
			if(change.id) {
				var id = '#' + change.id;
				if(parameter == 'innerHTML'){
					jQuery(id).html(value);
				}
				else{
					jQuery(id).attr(parameter, value);
				}
			}
			else if(change.name) {
				var name = change.name;
				jQuery('[name=' + name + ']').each(
					function() {
						jQuery(this).attr(parameter, value);
					}
				);
			}
		}
	}
}

function escapeHTML(str) {
   var div = document.createElement('div');
   var text = document.createTextNode(str);
   div.appendChild(text);
   return div.innerHTML;
}