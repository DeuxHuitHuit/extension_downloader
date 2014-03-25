/*
 * @author Deux Huit Huit
 */
(function ($) {
	
	"use strict";
	
	var SYM_URL = Symphony.Context.get('root') + '/symphony/';
	var BASE_URL = SYM_URL + 'extension/extension_downloader/';
	var DOWNLOAD_URL = BASE_URL + 'download/';
	var SEARCH_URL = BASE_URL + 'search/';
	var EXTENSIONS_URL = SYM_URL + 'system/extensions/';
	
	var win = $(window);
	
	var context;
	var wrap;
	var input;
	
	var searchTimer = 0;
	
	var queryStringParser = (function () {
		var
		a = /\+/g,  // Regex for replacing addition symbol with a space
		r = /([^&=]+)=?([^&]*)/gi,
		d = function (s) { return decodeURIComponent(s.replace(a, ' ')); },
		
		_parse = function (qs) {
			var 
			u = {},
			e,
			q;
			
			//if we dont have the parameter qs, use the window location search value
			if (qs !== '' && !qs) {
				qs = window.location.search;
			}
			
			//remove the first caracter (?)
			q = qs.substring(1);

			while ((e = r.exec(q))) {
				u[d(e[1])] = d(e[2]);
			}
			
			return u;
		};
		
		return {
			parse : _parse
		};
	})();
	
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
				document.location = EXTENSIONS_URL + '?download_handle=' + data.handle; 
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
		clearTimeout(searchTimer);
		if (e.which === 13) {
			download();
		} else {
			searchTimer = setTimeout(search, 300);	
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
	
	var selectExtension = function () {
		var qs = queryStringParser.parse();
		if (!!qs.download_handle) {
			var tr = $('#contents table td input[name="items[' + qs.download_handle + ']"]').closest('tr');
			tr.click();
			win.scrollTop(tr.position().top);
		}
	};
	
	var init = function () {
		injectUI();
		win.load(selectExtension);
	};
	
	$(init);
	
})(jQuery);
