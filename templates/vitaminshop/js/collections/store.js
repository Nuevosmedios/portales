var Products = Backbone.Collection.extend({
	model: Product,
	url: 'components/com_store/api.php?action=getAllProducts',
});
/*var Categories = Backbone.Collection.extend({
	model: Categories,
	url: 'api/api.php?action=get_all_schools',
});
var Cupons = Backbone.Collection.extend({
	model: Cupons,
	url: 'api/api.php?action=get_all_schools',
});*/