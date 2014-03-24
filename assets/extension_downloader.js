/*
 * @author Deux Huit Huit
 */
(function ($) {
	
	"use strict";
	
	var context;
	var wrap;
	var input;
	
	var download = function () {
		wrap.addClass('loading');
		$.post('/symphony/extension/extension_downloader/download/', {
				q: input.val()
			}, function (data) {
			if (data.success) {
				alert('Download completed! Page will refresh.');
				document.location.reload();
			} else {
				alert(data.error);	
			}
		}).fail(function (e) {
			alert('error');	
		}).always(function (e) {
			wrap.removeClass('loading');
		});
	};
	
	var keyup = function (e) {
		if (e.which === 13) {
			download();
		}
	};
	
	var injectUI = function () {
		context = $('#context');
		wrap = $('<div />').attr('id', 'extension_downloader');
		var title = $('<h3 />').text('Download extension');
		input = $('<input />').attr('placeholder',
			'zipball url, github-user/repo or extension_handle');
		
		wrap.append(title).append(input);
		context.append(wrap);
		
		input.keyup(keyup);
	};
	
	var init = function () {
		injectUI();
	};
	
	$(init);
	
})(jQuery);