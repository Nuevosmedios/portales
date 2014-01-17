$(document).ready(function() {
	$('.dropdown-toggle').dropdown();
	$('.parent').addClass('dropdown');
	$('.parent > a').addClass('dropdown-toggle');
	$('.parent > a').attr('data-toggle', 'dropdown');
	$('.parent > a').attr('href','#');
	$('.parent > a').append('<span class="caret"></span>');
	$('.parent > ul').addClass('dropdown-menu');
	$('.nav-child .parent').removeClass('dropdown');
	$('.nav-child .parent .caret').css('display', 'none');
	$('.nav-child .parent').addClass('dropdown-submenu');
});