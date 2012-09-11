jQuery(document).ready(function ($) {
	$("#addRedirection").css({'position':'absolute', 'display':'none'});
	$('#showAddRedirection').click(function () {
$("body").append("<div id='overlay-wrapper'></div>");
$("#overlay-wrapper")
.css({"position":"fixed",
"width":"100%",
"height":"100%",
"background-color":"gray",
"opacity":"0.6",
"top":"0",
"left":"0",
"z-index":"9996",
"overflow":"hidden","cursor":"auto"})
.click(function() {
	$('#addRedirection').slideUp(function() {
		$("#overlay-wrapper").remove();
	});
});
		$("#addRedirection")
			.addClass("defaults")
			.css('zIndex','9997')
			.css("left", Math.max(0, (($(window).width() - $('.defaults').width()) / 3) + $(window).scrollLeft()) + "px")
			.css("top", Math.max(0, (($(window).height() - ($('.defaults').height()+250)) / 3) + $(window).scrollTop()) + "px")
			.prepend('<span class="bClose">Close</span>')
			.slideDown();
		$(".bClose").click(function() {
			$('#addRedirection').slideUp(function() {
				$("#overlay-wrapper").remove();
			});
		});
	});
});