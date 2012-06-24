(function($){

	$("#Form_VariationForm select").change(function(){
		
		$("#Form_VariationForm option").hide(); // turn off all options
		
		var variationsjson = $.parseJSON($("#Form_VariationForm_VariationOptions").val());
		var currentValue = $(this).val();
		var showingSelects = [];
		// loop through objects and see if the current option value is in it
		for(variation in variationsjson){
			var x = variationsjson[variation];
			for(var key in x) {
				if(key == currentValue){ // if option is there add the object to an array
					showingSelects.push(x);
				}
			}
		}
		 // loop through the final options/ojects and show them if they exist
		for(i=0; i<showingSelects.length; i++){
			for(var u in showingSelects[i]) {
				$("#Form_VariationForm option[value=\""+u+"\"]").show();
				if(u == currentValue){ // show all options in the same select changes ??? not sure about this
					$("#Form_VariationForm option[value=\""+u+"\"]").parent().find("option").show();
				}
			}
		}
		
		$("#Form_VariationForm option").first().show(); // turn on the default option
		
		if(showingSelects.length == 0){ // if NO options show all options ( this should not really happen )
			$("#Form_VariationForm option").show();
		}
		
	});

})(jQuery);

