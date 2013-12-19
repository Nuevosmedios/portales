var AutoJavascriptValidation = new Class({
	
	initialize: function(form_name, rules) {
		this.run(form_name, rules);
	},

	run: function(form_name, rules) {
		var check_group = 1;
		for(rule in rules){
			rules[rule].each(function(field){
				var k = 0;
				if(($$("input[name='"+field+"']")[k] != null) && $$("input[name='"+field+"']")[k].get('type') == 'hidden'){
					k = 1;
				}
				if(($$("input[name='"+field+"']")[k] != null)){
					$$("input[name='"+field+"']")[k].addClass("validate['"+rule+"']");
				}
				if(($$("select[name='"+field+"']")[k] != null)){
					$$("select[name='"+field+"']")[k].addClass("validate['"+rule+"']");
				}
				if(($$("textarea[name='"+field+"']")[k] != null)){
					$$("textarea[name='"+field+"']")[k].addClass("validate['"+rule+"']");
				}
				if(($$("input[name='"+field+"[]']")[0] != null)){
					$$("input[name='"+field+"[]']").each(function(check){
						check.addClass("validate['group["+check_group+"]']");
					});
					check_group = check_group + 1;
				}
			});
		};
	}

});