/* JCE Editor - 2.3.4.1 | 23 November 2013 | http://www.joomlacontenteditor.net | Copyright (C) 2006 - 2013 Ryan Demmer. All rights reserved | GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html */
(function($){$.jce.Users={select:function(){var u=[],v,o,h,s=window.parent.document.getElementById('users');$('input:checkbox:checked').each(function(){v=$(this).val();if(u=document.getElementById('username_'+v)){h=$.trim(u.innerHTML);if($.jce.Users.check(s,v)){return;}
var li=document.createElement('li');li.innerHTML='<input type="hidden" name="users[]" value="'+v+'" /><label><span class="users-list-delete"></span>'+h+'</label>';s.appendChild(li);}});this.close();},check:function(s,v){$.each(s.childNodes,function(i,n){var input=n.firstChild;if(input.value===v){return true;}});return false;},close:function(){var win=window.parent;if(typeof win.SqueezeBox!=='undefined'){win.SqueezeBox.close();}}};$(document).ready(function(){$('#cancel').click(function(e){$.jce.Users.close();e.preventDefault();});$('#select').click(function(e){$.jce.Users.select();e.preventDefault();});});})(jQuery);