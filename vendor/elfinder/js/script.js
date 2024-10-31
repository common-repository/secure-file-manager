(function($) {
	$(document).ready(function() {
		$('#elfinder').elfinder(
			{
				cssAutoLoad : false,               // Disable CSS auto loading
				baseUrl : './',                    // Base URL to css/*, js/*
				url : elfScript.pluginsDirUrl + '/vendor/elfinder/php/connector.minimal.php',
				customData : {_wpnonce: elfScript.sfmpNonceKey },
			},
			function(fm, extraObj) {
				// `init` event callback function
				fm.bind('init', function() {
					// Optional for Japanese decoder "encoding-japanese.js"
					if (fm.lang === 'ja') {
						fm.loadScript(
							[ '//cdn.rawgit.com/polygonplanet/encoding.js/1.0.26/encoding.min.js' ],
							function() {
								if (window.Encoding && Encoding.convert) {
									fm.registRawStringDecoder(function(s) {
										return Encoding.convert(s, {to:'UNICODE',type:'string'});
									});
								}
							},
							{ loadType: 'tag' }
						);
					}
				});
				var title = document.title;
				fm.bind('open', function() {
					var path = '',
						cwd  = fm.cwd();
					if (cwd) {
						path = fm.path(cwd.hash) || null;
					}
					document.title = path? path + ':' + title : title;
				}).bind('destroy', function() {
					document.title = title;
				});
			}
		);
	});
})( jQuery );