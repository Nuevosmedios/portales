//View for available contents 
var StoreView = Backbone.View.extend({

	//The element it's a div with class container
	tagName: "div",
	className: "container",

	//Let's call the render function whent we creaete the view
	initialize: function(){
		this.render();
	},

	//Cache the template compiled by Handlebars
	template: Handlebars.compile($('#school-view-tpl').html()),

	//The events for this view
	events: {
		//'click #selected_school':'get_data'
		'change #schoolList':'get_data'
	},

	//Render function for this view
	render: function(){

		//Parse the collection to JSON then append it to tab
		this.$el.html(this.template(this.collection.toJSON()));
		return this;
	},

	get_data: function(ev){
		var coll = new Data([], { id: $('#schoolList').select2("val") });
		coll.fetch({
			success: function(){
				var newView = new DataView({collection: coll});
				$('#data-view').html(newView.$el);
			},
			error: function(){

			},
		});
	}
});

var DataView = Backbone.View.extend({
	//The element it's a div with class container
	tagName: "div",
	className: "container",

	//Let's call the render function whent we creaete the view
	initialize: function(){
		this.render();
	},

	//Cache the template compiled by Handlebars
	template: Handlebars.compile($('#data-view-tpl').html()),

	//The events for this view
	events: {

	},

	//Render function for this view
	render: function(){
		console.log(this.collection.toJSON());
		//Parse the collection to JSON then append it to tab
		this.$el.html(this.template(this.collection.toJSON()));
		return this;
	}
});