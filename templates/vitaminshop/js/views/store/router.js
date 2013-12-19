var AppRouter = Backbone.Router.extend({
	routes: {
		'': 'product_view'
	},
	pattern: function(Collection, View, fSelector, fInnerSelector, extra){

		//Make some cache for the DOM elements
		var $fSelector = $('#'+fSelector);

		//When the route is viewed directly set the tab element active
		$fSelector.addClass('active').siblings().removeClass('active');

		//Initialize the collection
		var innerCollection = new Collection();

		//Make the fetch
		innerCollection.fetch({
			beforeSend: function(){
				$('#school-view').find('.load').show();
			},
			success: function(){
				//Then initialize the view and append its results
				var newView = new View({collection: innerCollection});
				if(fInnerSelector){
					var $fInnerSelector = $('#'+fInnerSelector);
					$fInnerSelector.html(newView.$el);
				}
				else{
					$fSelector.html(newView.$el);
				}
				$("#schoolList").select2({
					minimumInputLength: 5,
					allowClear: true
				});
				$('#school-view').find('.load').hide();
			},

			error: function(){
				//It's a mistake that I used to know...
				console.log("Something has happened, only God knows");
			}
		});
	},
	school_view: function(){
		this.pattern(Products, SchoolView, 'school-view');
	},
	data_view: function(){
		this.pattern(Datas, DataView, 'data-view');
	},
});