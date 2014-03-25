/*
 * @author Deux Huit Huit
 */
(function ($) {
	
	"use strict";
	
	var BASE_URL = Symphony.Context.get('root') + '/symphony/extension/extension_downloader/';
	var DOWNLOAD_URL = BASE_URL + 'download/';
	var SEARCH_URL = BASE_URL + 'search/';
	
	var context;
	var wrap;
	var input;
	
	var error = function (data) {
		alert(data.error || 'Unknown error');
	};
	
	var httpError = function (e) {
		alert('HTTP error');
	};
	
	var search = function () {
		var data = {
			q: input.val()
		};
		
		wrap.addClass('loading');
		
		$.post(SEARCH_URL, data, function (data) {
			if (data.success && data.results) {
				console.log(data.results);
			} else {
				error(data);
			}
		}).fail(httpError).always(function (e) {
			wrap.removeClass('loading');
		});
	};
	
	var download = function () {
		var data = {
			q: input.val()
		};
		
		wrap.addClass('loading');
		input.attr('disabled', 'disabled').blur();
		
		$.post(DOWNLOAD_URL, data, function (data) {
			if (data.success) {
				alert('Download completed! Page will refresh.');
				document.location.reload();
			} else {
				error(data);
			}
		}).fail(httpError).always(function (e) {
			wrap.removeClass('loading');
			input.removeAttr('disabled');
			input.focus();
		});
	};
	
	var keyup = function (e) {
		if (e.which === 13) {
			download();
		} else {
			search();	
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
