/*$(function() {
	$("#cSilueta2").hover(function(e){
		if(!$('#cMessageActualidad').is(":visible")){
			var m = $('#cMessageToClone').clone();
			$(m).removeAttr('id').removeClass('cMessageToClone').addClass('cMessage').attr('id','cMessageActualidad');
			$(m).find('h1').html('Actualidad');
			$(m).find('div').html('Opina y propone temas de actualidad para la comunidad');
			$('#message').html(m);
			$(m).fadeIn('slow',function() { });
		}
	}//, function(e) { $('#cMessageActualidad').fadeOut('slow',function() { }); }
	);
	$("#cSilueta1").hover(function(e){
		if(!$('#cMessageActualidad').is(":visible")){
			var m = $('#cMessageToClone').clone();
			$(m).removeAttr('id').removeClass('cMessageToClone').addClass('cMessage').attr('id','cMessageActualidad');
			$(m).find('h1').html('Actualidad');
			$(m).find('div').html('Opina y propone temas de actualidad para la comunidad');
			$('#message').html(m);
			$(m).fadeIn('slow',function() { });
		}
	});
});*/

function showMessage(zone,title,message,link){
	$('#'+zone).hover(function(e){
		if(!$('#'+title).is(':visible')){
			var m = $('#cMessageToClone').clone();
			$(m).removeAttr('id').removeClass('cMessageToClone').addClass('cMessage').attr('id',title);
			$(m).find('h1').html(title);
			$(m).find('div').html(message);
			$(m).find('a').attr('href',link).attr('title',title);
			$('#message').html(m);
			$(m).fadeIn('slow',function() { });
		}
	});
}

/*On document ready*/
$(document).ready(function() {
	showMessage('cSilueta2','Actualidad','Opina y propone temas de actualidad para la comunidad','/index.php/foro/26-actualidad.html');
	showMessage('cSilueta1','Social','Opina y propone temas de sociales para la comunidad','/index.php/foro/30-social.html');
	showMessage('cSilueta3','Entretenimiento','Opina y propone temas de entretenimiento para la comunidad','/index.php/foro/28-entretenimiento.html');
});