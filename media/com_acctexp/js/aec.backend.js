jQuery(document).ready(function(jQuery) {
	jQuery.expr[':'].Contains = function(a,i,m){
		return jQuery(a).text().toLowerCase().indexOf(m[3].toLowerCase())>=0;
	};

	jQuery('.aec-navbar').hover(function(){
		if ( jQuery(".navbar-hover-helper").length == 0 ) {
			jQuery('.aec-navbar').addClass("navbar-hover-helper");
			jQuery('.aec-buttons-fixed').addClass("aec-buttons-fixed-extended");

			jQuery('div.navbar-fixed-top').addClass("navbar-fixed-top-override").prepend("<div class=\"head-minify btn btn-inverse\"><i class=\"icon-chevron-left icon-white bsicon-chevron-left bsicon-white\"></i></div>");

			jQuery(".aec-navbar").on( "click", ".head-minify", function(){
				jQuery('.head-minify').remove();
				jQuery('.navbar-fixed-top').removeClass('navbar-fixed-top-override');
				jQuery('.aec-buttons-fixed').removeClass('aec-buttons-fixed-extended');
			});
		}

		}, function(){
			jQuery('.aec-navbar').removeClass("navbar-hover-helper");
		}
	);

	jQuery('#module-status span.multilanguage a').removeClass('modal');

	if ( jQuery("#system-message li").length != 0 ) {
		jQuery("div.container").before('<span class="nav-msg label label-warning">'+jQuery("#system-message li").html()+'</span>')
			.parent().children(".nav-msg").fadeIn(500,function(){jQuery(".nav-msg").addClass("nav-msg-transition");});
	}

	jQuery('#quicksearch').focus(function() {
		jQuery('#quicksearch').popover({ trigger:'manual', placement:'bottom' });
	})

	var typingTimer;

	jQuery("#quicksearch").on({
		'keypress' : function(e) { if (e.keyCode == 13) return false; },
		'keyup' : function(e) { clearTimeout(typingTimer); inputString = this.value; typingTimer = setTimeout(lookup, "300"); },
		'keydown' : function(e) { jQuery('.popover .popover-content p').html("Searching..."); },
		'focusin' : function(e) {
			jQuery("#quicksearch").popover();

			if ( this.value != "" ) {
				inputString = this.value;

				jQuery('.popover .popover-content p').html("Searching...");

				typingTimer = setTimeout(lookup, "100");
			}
		},
		'focusout' : function(e) {
			jQuery(this).popover('hide');
		}
	});

	jQuery("#settings-filter").on({
		'keypress' : function(e) { if (e.keyCode == 13) return false; },
		'keyup' : function(e) { clearTimeout(typingTimer); inputString = this.value; typingTimer = setTimeout(settingsfilter, "100"); }
	});

	jQuery('form#adminForm').one('click', function() {
		jQuery('div.aec-buttons a.btn').attr("disabled", false);
	});

	jQuery('label.toggleswitch').one('click', function() {
		jQuery('div.aec-buttons a.btn').attr("disabled", false);
	});

	jQuery('a.quicksearch').on("click", function(e) {
		e.preventDefault();

		jQuery("#quicksearch").popover('show');

		jQuery("input#quicksearch").val( this.text ).focus();
	});

	jQuery('select[name="color"]').simplecolorpicker();

	jQuery('a#testexport').on("click", function(e) {
		var values = {};
		jQuery.each(jQuery('#adminForm').serializeArray(), function(i, field) {
		    if ( typeof values[field.name] != 'undefined' ) {
		    	if ( typeof values[field.name] != 'object' ) {
		    		var temparray = new Array();
		    		temparray.push( values[field.name] );
		    		temparray.push( field.value );

		    		values[field.name] = temparray;
		    	} else {
		    		values[field.name].push( field.value );
		    	}
		    } else {
		    	values[field.name] = field.value;
		    }
		});

		values.task = 'testexport'+values.returnTask;
		values.export_method = 'test';

		jQuery('#export-result').html('<p>Loading...</p>');

		jQuery.post("index.php?option=com_acctexp" , values, function(data) {
			jQuery('#export-result').html(data);
		});

	});

	// JCE is rather large
	if ( typeof(WFEditor) !== undefined ) {
		if ( jQuery(document).width() < 1350 ) {
			if ( jQuery(document).width() < 1000 ) {
				jQuery(".form-horizontal .control-group > label").css("width","30%");
				jQuery(".form-horizontal .controls").css("margin-left","34%");
			} else {
				jQuery(".form-horizontal .controls").css("margin-left","50%");
			}
		}
	}

	// fix sub nav on scroll - adapted, against better advice, from http://twitter.github.com/bootstrap/less.html
	var tbFixed = 0, btFixed = 0, navTop = 0, navBtn = 0;

	processScroll();

	jQuery(window).on('scroll', processScroll);

	function processScroll() {
		var i, scrollTop = jQuery(window).scrollTop();

		if ( navTop == 0 ) {
			navTop = jQuery('.aec-navbar .navbar-inner').offset().top;
		}

		if (scrollTop >= navTop && !tbFixed) {
			tbFixed = 1;
			jQuery('.aec-navbar').addClass('navbar-fixed-top');
			jQuery('nav.navbar').addClass('navbar-fixed-top-minified');
		} else if (scrollTop <= navTop && tbFixed) {
			tbFixed = 0;
			jQuery('nav.navbar').removeClass('navbar-fixed-top-minified');
			jQuery('.aec-navbar').removeClass('navbar-fixed-top').removeClass('navbar-fixed-top-override');
			jQuery('div.head-minify').remove();
			jQuery('.aec-buttons-fixed').removeClass("aec-buttons-fixed-extended");
		}

		if ( jQuery('.aec-buttons').length ) {
			if ( navBtn == 0 ) {
				navBtn = jQuery('.aec-buttons').offset().top - 42;
			}

			if (scrollTop >= navBtn && !btFixed) {
				btFixed = 1;
				jQuery('.aec-buttons').addClass('aec-buttons-fixed');
			} else if (scrollTop <= navTop && btFixed) {
				btFixed = 0;
				jQuery('.aec-buttons').removeClass('aec-buttons-fixed');
			}
		}
	}

	function lookup() {
		jQuery.post("index.php?option=com_acctexp&task=quicksearch&search="+inputString , {queryString: ""+inputString+""}, function(data) {
			jQuery('.popover .popover-content p').html(data);
		});
	}

	function settingsfilter() {
		if ( inputString == "" ) {
			jQuery(".setting-filter-mute, .setting-filter-hide, .setting-filter-hide-override, .affixnav a").removeClass("setting-filter-mute setting-filter-hide setting-filter-hide-override");
			return;
		}

		jQuery(".control-group, section h2, section .accordion, .page-header, section").removeClass("setting-filter-mute setting-filter-hide setting-filter-hide-override");

		jQuery('.control-group').filter( function() {
			var matches = jQuery('label.control-label:Contains("'+inputString+'"), label.control-label[name*="'+inputString+'"]', this).length;
			return matches < 1;
		}).each( function() {
			jQuery(this).addClass("setting-filter-mute");
			jQuery(this).parent("section").addClass("setting-filter-hide");
			jQuery(this).prevAll("h2").first().addClass("setting-filter-hide");
		});

		jQuery('.control-group').filter( function() {
			var matches = jQuery('label.control-label:Contains("'+inputString+'"), label.control-label[name*="'+inputString+'"]', this).length;
			return matches > 0;
		}).each( function() {
			jQuery(this).parent("section").addClass("setting-filter-hide-override")
			jQuery(this).prevAll("h2").first().addClass("setting-filter-hide-override");
		});

		jQuery("section h2:not(.setting-filter-hide-override)").nextUntil("h2", ".control-group").addClass("setting-filter-hide");

		jQuery('.page-header').filter( function() {
			return jQuery(this).nextUntil( '.page-header', 'section.setting-filter-hide-override' ).length < 1;
		}).each( function() {
			jQuery(".affixnav a[href$=\""+this.id+"\"]").addClass("setting-filter-mute");
			jQuery(this).addClass("setting-filter-hide");
		});

		jQuery('.page-header').filter( function() {
			return jQuery(this).nextUntil( '.page-header', 'section.setting-filter-hide-override' ).length > 0;
		}).each( function() {
			jQuery(".affixnav a[href$=\""+this.id+"\"]").removeClass("setting-filter-mute");
			jQuery(this).addClass("setting-filter-hide-override");
		});

	}

	jQuery('div.aec-buttons').tooltip({placement: "bottom", selector: 'a.btn', delay: { show: 300, hide: 100 }});
	jQuery('table.aecadminform').tooltip({placement: "bottom", selector: 'a.btn', delay: { show: 300, hide: 100 }});
	jQuery('div.control-group').tooltip({placement: "right", selector: '.bstooltip', delay: { show: 300, hide: 400 }});

	jQuery('.jqui-datepicker').datepicker({ dateFormat: 'yy-mm-dd', changeMonth: true, changeYear: true, showWeek: true, showOtherMonths: true, selectOtherMonths: true });
	jQuery('.jqui-datetimepicker').datetimepicker({ dateFormat: 'yy-mm-dd', timeFormat: 'hh:mm:ss', changeMonth: true, changeYear: true, showWeek: true, showOtherMonths: true, selectOtherMonths: true, showSecond: true, hourGrid: 6, minuteGrid: 10, secondGrid: 10  });

	jQuery('.jqui-multiselect').multiselect({ noneSelectedText: 'Select', selectedList: 8 });

	jQuery('#drilldown').menu({
		content: jQuery('#drilldown').next().html(),
		backLink: false,
		width: "260px",
		maxHeight: 286,
		topLinkText: 'Root',
		select: function(item) {
			jQuery('#name').val(jQuery(".mi-menu-mi-name", item).html());
			jQuery('#desc').val(jQuery(".mi-menu-mi-desc", item).html());
			jQuery('#class_name').val(item.hash.slice(1));
			jQuery('a#drilldown').html("Selected: <span class=\"label label-important\">"+jQuery(".mi-menu-mi-name", item).html()+"</span>");
		}
	});

	if ( jQuery('.jqui-daterangepicker').length ) {
		jQuery('.jqui-daterangepicker').daterangepicker({
			latestDate: Date.parse('today'),
			dateFormat: "yy-mm-dd",
			constrainDates: true,
			datepickerOptions: {	dateFormat: 'yy-mm-dd',
									changeMonth: true,
									changeYear: true,
									showWeek: true,
									showOtherMonths: true,
									selectOtherMonths: true,
									maxDate: Date.parse('today'),
									defaultDate: Date.parse('today'),
									gotoCurrent: true
								},
			onClose:function(){
				var range = jQuery('.jqui-daterangepicker').val();
	
				if ( range.indexOf(" - ") === -1 ) {
					var rangestart = range;
					var rangeend = range;
				} else {
					var rangestart = range.slice(0, 10);
					var rangeend = range.slice(13);
				}

				cf.range( rangestart+" 00:00:00", rangeend+" 23:59:59")
				.update({ start: rangestart+" 00:00:00", end: rangeend+" 23:59:59"});
			}
		});
	}

	jQuery(".collapse").collapse({toggle: false});
});

function readNotice(id) {
	jQuery.post("index.php?option=com_acctexp&task=readNoticeAjax&id="+id , {queryString: ""}, function(data) {
		jQuery('#alert-'+id).remove();

		jQuery.post("index.php?option=com_acctexp&task=getNotice" , {queryString: ""}, function(data) {
			jQuery('#aec-alertlist').append(data);
		});

		if ( jQuery('#further-notices>span').html() ) {
			if ( ( jQuery('#further-notices>span').html() - 1 ) > 0 ) {
				jQuery('#further-notices>span').html( jQuery('#further-notices>span').html() - 1 );
			} else {
				jQuery('#further-notices').remove();
			}
		}
	});
}

function readNotices() {
	jQuery.post("index.php?option=com_acctexp&task=readNoticesAjax" , {queryString: ""}, function(data) {
		jQuery('#notifications').modal('hide');
		jQuery('a#aecmenu-notifications').remove();
	});
}

function toggleProperty(type, property, id, callerid, callerclass) {
	if ( jQuery('#'+callerid).hasClass('ui-disabled') ) { 
		return;
	}

	if ( property == 'default' ) {
		jQuery('.'+callerclass+' i').addClass('bsicon-refresh');
		jQuery('.'+callerclass+' i')
			.removeClass('bsicon-remove')
			.removeClass('bsicon-star-empty')
			.removeClass('bsicon-ok')
			.removeClass('bsicon-eye-open')
			.removeClass('bsicon-star');
		jQuery('.'+callerclass+' i').addClass('bsicon-rotate');
	} else {
		jQuery('#'+callerid+' i').addClass('bsicon-refresh');
		jQuery('#'+callerid+' i')
			.removeClass('bsicon-remove')
			.removeClass('bsicon-stop')
			.removeClass('bsicon-ok')
			.removeClass('bsicon-eye-open')
			.removeClass('bsicon-star');
		jQuery('#'+callerid+' i').addClass('bsicon-rotate');
	}

	if ( jQuery('#'+callerid).hasClass('btn-toggle-danger') ) {
		jQuery('#'+callerid).removeClass('btn-toggle-danger').addClass('btn-toggle-warning');
	} else {
		jQuery('#'+callerid).removeClass('btn-toggle-success').addClass('btn-toggle-warning');
	}

	jQuery('#'+callerid+' i').addClass('bsicon-refresh').removeClass('bsicon-eye-open');

	jQuery.post("index.php?option=com_acctexp&task=toggleAjax&type="+type+"&property="+property+"&id="+id , {queryString: ""}, function(data) {
		jQuery('#'+callerid).removeClass('btn-toggle-warning');

		if ( data == "1" ) {
			jQuery('#'+callerid).addClass('btn-toggle-success');

			if ( property == 'default' ) {
				jQuery('#'+callerid+' i').addClass('bsicon-star').removeClass('bsicon-refresh').removeClass('bsicon-rotate');
				jQuery('#'+callerid).attr('disabled','disabled').addClass('ui-disabled');
				jQuery('.'+callerclass+':not(#'+callerid+') i').addClass('bsicon-star-empty').removeClass('bsicon-refresh').removeClass('bsicon-rotate');
				jQuery('.'+callerclass+':not(#'+callerid+')').removeAttr('disabled').removeClass('ui-disabled').removeClass('btn-toggle-success').addClass('btn-toggle-danger');
			} else if ( property == 'visible' ) {
				jQuery('#'+callerid+' i').addClass('bsicon-eye-open').removeClass('bsicon-refresh').removeClass('bsicon-rotate');
			} else {
				jQuery('#'+callerid+' i').addClass('bsicon-ok').removeClass('bsicon-refresh').removeClass('bsicon-rotate');
			}
		} else {
			jQuery('#'+callerid).addClass('btn-toggle-danger');

			jQuery('#'+callerid+' i').addClass('bsicon-remove').removeClass('bsicon-refresh').removeClass('bsicon-rotate');
		}
	});
}

function addGroup(type, callerid) {
	group = jQuery('select#add_group').val();
	id = jQuery('input:hidden[name=id]').val();

	if ( group > 0 ) {
		jQuery('#'+callerid).attr('disabled','disabled');
		jQuery('#'+callerid+' i').addClass('bsicon-rotate');

		jQuery.post("index.php?option=com_acctexp&task=addGroupAjax&type="+type+"&group="+group+"&id="+id , {queryString: ""}, function(data) {
			if ( data == "0" ) {
	
			} else if ( data.length < 500 ) {
				jQuery('#'+callerid+' i').removeClass('bsicon-rotate');

				jQuery('select#add_group option[value=\''+group+'\']').attr('disabled','disabled');

				jQuery('.aec-grouplist tbody').append(data);
			}

			jQuery('#'+callerid).removeAttr('disabled');
			jQuery('select#add_group').val("0")
		});
	}
}

function removeGroup(type, group, callerid) {
	if ( group > 0 ) {
		jQuery('#'+callerid).attr('disabled','disabled');
		jQuery('#'+callerid+' i').addClass('bsicon-rotate');

		id = jQuery('input:hidden[name=id]').val();

		jQuery.post("index.php?option=com_acctexp&task=removeGroupAjax&type="+type+"&group="+group+"&id="+id , {queryString: ""}, function(data) {
			if ( data == "1" ) {
				jQuery('#'+callerid+' i').removeAttr('disabled').removeClass('bsicon-rotate');

				jQuery('select#add_group option[value=\''+group+'\']').removeAttr('disabled');

				jQuery('#row-group-'+group).remove();
			}

			jQuery('select#add_group').val("0")
		});
	}
}