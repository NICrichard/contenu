(function($) {
	'use strict';

	/**
	 * All of the code for your public-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

	window.dataTable_selected = {};

	$('form.wpcf7-form').on('reset', function() {

		if ($(this).find('.dataTable').data('name').length) {
			var groupName = $(this).find('.dataTable').data('name');
			delete window.dataTable_selected[groupName];
			$('input[type="hidden"][name="'+groupName+'"]').val('');
		}

	});

	$('.dataTable').on('click', 'input:radio, input:checkbox', function() {

		var groupName = $(this).attr('name').replace('[]', '');

		/**
		 * checks if the input belongs to an array.
		 */
		if ($(this).attr('type') === 'checkbox') {
			if (!window.dataTable_selected[groupName]) {
				window.dataTable_selected[groupName] = [];
			}

			if ($.inArray($(this).val(), window.dataTable_selected[groupName]) === -1 && $(this).prop('checked')) {
				window.dataTable_selected[groupName].push($(this).val());
			} else {
				var index = $.inArray($(this).val(), window.dataTable_selected[groupName]);
				if (index !== -1) {
					window.dataTable_selected[groupName].splice(index, 1);
				}
			}

			$('input[type="hidden"][name="'+groupName+'"]').val(window.dataTable_selected[groupName].join(', '));

		} else {
			if ($(this).prop('checked')) {
				window.dataTable_selected[groupName] = $(this).val();
			} else {
				window.dataTable_selected[groupName] = false;
			}

			$('input[type="hidden"][name="'+groupName+'"]').val(window.dataTable_selected[groupName]);
		}

	});

	$('.dataTable').on('draw.dt', function() {
		$.each(window.dataTable_selected, function(name) {
			if ($.isArray(this)) {

				var selected = this;

				$('.dataTable input:checkbox[name="'+name+'[]"]').each(function() {
					if ($.inArray($(this).val(), selected) !== -1) {
						$(this).prop('checked', true);
					}
				});
			} else {
				var $radios = $('.dataTable input:radio[name="'+name+'"]');
				$radios.filter('[value='+this+']').prop('checked', true);
			}
		});

	});

})(jQuery);
