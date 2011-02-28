
// 
(function($) {
	$(document).ready(function() {

		// We're avoiding livequery initializers for now,
		// to be replaced by jQuery.entwine.
		Behaviour.register({	
		});

		$('#right input[name=action_doImportLabels]').live('click', function(){
			var form = $('#right form');
			var formAction = form.attr('action') + '?' + $(this).fieldSerialize();

			var response = confirm("Do you really want to update the labels for this feature type?");
			if (response == false) {
				return false;
			}
			
			// @todo TinyMCE coupling
			if(typeof tinyMCE != 'undefined') tinyMCE.triggerSave();

			// Post the data to save
			$.post(formAction, form.formToArray(), function(result){
				// @todo TinyMCE coupling
				tinymce_removeAll();

				$('#right #ModelAdminPanel').html(result);

				if($('#right #ModelAdminPanel form').hasClass('validationerror')) {
					statusMessage(ss.i18n._t('ModelAdmin.VALIDATIONERROR', 'Import failed.'), 'bad');
				} else {
					statusMessage(ss.i18n._t('ModelAdmin.SAVED', 'Imported successfully.'), 'good');
				}

				Behaviour.apply(); // refreshes ComplexTableField
				if(window.onresize) window.onresize();
			}, 'html');

			return false;
		});

	})
})(jQuery);


