var API = "http://" + window.location.hostname + "/~pablocpantoja/vitaminshop/components/com_store/api.php";

$(document).ready(function() {
	getAllProducts();
	getRules('Pay method');
	/*if($.cookie('shoppingcart')){
		var cart = $.cookie('shoppingcart');
	} else {
		$.cookie('shoppingcart','0');
	}*/
});

function getAllProducts(){
	$.ajax({  
		url: API,
		dataType: 'json',
		data: { "action":"getAllProductsByBrand" },
		async: true,
		success: function(data){
			showProducts(data);
		}
	});
}

function productHasCoupon(id){
	$.ajax({  
		url: API,
		dataType: 'json',
		data: { "action":"productHasCoupon","pid": id },
		async: true,
		success: function(data){
			if(data[0].coupons == "0"){
				addToCartAction(id,"nomodal");
			} else {
				addToCartAction(id,"modal");
				showMessage("Hey!!! we have rules for objects: <b>Coupon</b> and <b>Pay method</b> <a class='btn btn-default btn-xs' href='#' data-toggle='modal' data-target='#rule'>View rules in JSON Notation</a>","general-message");
				//
			}
		}
	});
}

function showMessage(message,container){
	$('#' + container).empty().append(message).removeClass('hidden');
}

function addToCartAction(id,modal){
	switch(modal){
		case "modal":
			if($('#user-id').length != 0){
				$('#'+id).attr("data-toggle","modal").attr('data-target','#modal-' + id);
				couponModal(id);
				$('#product-'+id).find('.caption').find('p').append('<span class="label label-success">Has coupon</span>');
				getProductCoupons(id);
			} else {
				showMessage("Login to get discounts on our products","store-message");
			}
		break;
		case "nomodal":
			if($('#user-id').length != 0){
				//showMessage("Suscribe to our loyalty system to get discounts in our products");
			}
			else {
				showMessage("Not an user, login to get discounts on our products","store-message");
			}
		break;
	}
}

function getProductCoupons(id){
	$.ajax({  
		url: API,
		dataType: 'json',
		data: { "action":"getProductCoupons", "pid":id },
		async: false,
		success: function(data){
			$.each(data,function(k, v) {
				if(v.id_acoupon_type == jQuery.cookie('disp')){
					//console.log(v.id_acoupon_type);
					if(v.id_acoupon_type=="2"){
						var p = $('#product-' + id).find('.caption').find('h3').text();
						var price = parseFloat(p.substring(1)).toFixed(2);
						var disc = parseInt(v.discount);
						var perc = disc/100;
						var newprice = price - (price * perc.toFixed(2));
						$('#modal-' + id).find('.modal-body').append('<div class="pricep hidden">'+ price +'</div>');
						$('#modal-' + id).find('.store-bonus-apply').append('<span class="label label-danger">Not applied because you are on Web</span>');
						$('#modal-' + id).find('.web-bonus-apply').append('<span class="label label-success">Yes</span> Discount: ' + disc + '%, if you pay with Cash, Credit, ATM or Check');
						if(jQuery.cookie('rule')=="Rule 2"){
							$('#modal-' + id).find('.app-bonus').html('');
							$('#modal-' + id).find('.app-bonus-apply').append('');
						}
						if(jQuery.cookie('rule')=="Rule 2 Modified"){
							$('#modal-' + id).find('.app-bonus-apply').append('<span class="label label-danger">Not applied because you are on Web</span>');
						}
						$('#modal-' + id).find('.new-price').append('<h3>Discount price:</h3>');
						$('#modal-' + id).find('.new-price-value').append('<h3>$ ' + parseFloat(newprice).toFixed(2) + '</h3>');
						$('#product-' + id).find('h3').html('<span class="" style="text-decoration:line-through; margin-right:10px; position:relative; top:3px;">' + p + '</span>');
						$('#product-' + id).find('h3').append('<span class="label label-success">$ ' + parseFloat(newprice).toFixed(2) + '</span>');
					}
					if(v.id_acoupon_type=="3"){
						var p = $('#product-' + id).find('.caption').find('h3').text();
						var price = parseFloat(p.substring(1)).toFixed(2);
						var disc = parseInt(v.discount);
						var perc = disc/100;
						var newprice = price - (price * perc);
						$('#modal-' + id).find('.modal-body').append('<div class="price hidden">'+ price +'</div>');
						$('#modal-' + id).find('.store-bonus-apply').append('<span class="label label-danger">Not applied because you are on App</span>');
						$('#modal-' + id).find('.web-bonus-apply').append('<span class="label label-danger">Not applied because you are on App</span>');
						if(jQuery.cookie('rule')=="Rule 2"){
							$('#modal-' + id).find('.app-bonus').html('');
							$('#modal-' + id).find('.app-bonus-apply').append('');
						}
						if(jQuery.cookie('rule')=="Rule 2 Modified"){
							$('#modal-' + id).find('.app-bonus-apply').append('<span class="label label-success">Yes</span> Discount: ' + disc + '%, if you pay with Cash, Credit, ATM or Check');
						}
						$('#modal-' + id).find('.new-price').append('<h3>Discount price:</h3>');
						$('#modal-' + id).find('.new-price-value').append('<h3>$ ' + parseFloat(newprice).toFixed(2) + '</h3>');
						$('#product-' + id).find('h3').html('<span class="" style="text-decoration:line-through; margin-right:10px; position:relative; top:3px;">' + p + '</span>');
						$('#product-' + id).find('h3').append('<span class="label label-success">$ ' + parseFloat(newprice).toFixed(2) + '</span>');
					}
				}
			});
		}
	});
}

function couponModal(id){
	var coupon = $('#toClone').find('.modal-toclone').clone();
	$(coupon).removeClass('modal-toclone').addClass('modal');
	$(coupon).attr('id','modal-' + id);
	$(coupon).find('.modal-footer').find('.buy').attr('id','buy-' + id);
	$(coupon).find('.modal-footer').find('.buy').click(function(e){
		var pd = $('#modal-' + id).find('.new-price-value').text();
		var p = $('#modal-' + id).find('.pricep').text();
		$('#modal-' + id).modal('toggle');
		$('body').removeClass('modal-open');
		$('.modal-backdrop').remove();
		$('#paymentMethod').modal('show');
		$('#paymentMethod').find('.modal-body').find('h3').remove();
		$('#paymentMethod').find('.modal-body').append('<h3 class="price col-sm-offset-2">Price: '+ pd +'</h3>');
		po = parseInt(jQuery.cookie('loyal-points')) + ((p/100) * parseInt(jQuery.cookie('loyal-points')));
		console.log('Loyal: ' + parseInt(jQuery.cookie('loyal-points')) + ' Price: ' + p);
		jQuery.cookie('points',po);
		$("input[name='optionsRadios']").change(function(){
			if($(this).val() == "loyalty"){
				$('.price').find('h3').remove();
				$('.price').html('<h3 class="price">Price:  $ ' + p + ' as <i class="fa fa-trophy"></i> ' + parseInt((p*100)) + ' <small>no discounts applied</small>');
				po = jQuery.cookie('loyal-points') - (p*100);
				jQuery.cookie('points',po);
			} else {
				$('.price').empty();
				$('.price').html('<h3 class="price">Price: ' + pd);
				po = parseInt(jQuery.cookie('loyal-points')) + ((p*10));
				jQuery.cookie('points',po);
			}
		});
		$('#loyal-payment').find('label').find('span').remove();
		$('#loyal-payment').find('label').append('<span class="label label-info" id="loyalty-points"><i class="fa fa-trophy"></i> ' + parseInt(jQuery.cookie('loyal-points')) + '</span>');
		$('#paymentMethod').find('.modal-footer').find('.apply-buy').attr('id','apply-buy-'+ id);
		$('#paymentMethod').find('.modal-footer').find('.apply-buy').click(function(e){
			$.ajax({  
				url: API,
				dataType: 'json',
				data: { "action":"setUserLoyaltyPoints", "uid": $('#user-id').text(), "points": jQuery.cookie('points') },
				async: true,
				success: function(data){
					$('#paymentMethod').modal('toggle');
					$('body').removeClass('modal-open');
					$('.modal-backdrop').remove();
					$('#paymentCompleted').modal('show');
					$('#payment-data').append("<h3>New loyalty points amount: "+ parseInt(jQuery.cookie('points')) +"</h3>");
					$('#finish-order').click(function(e){
						$('#paymentCompleted-' + id).modal('toggle');
						$('body').removeClass('modal-open');
						$('.modal-backdrop').remove();
						location.reload();
					});
				}
			});
		});
	});
	$('#catalogue').append(coupon);
}

function showProducts(data){
	var toclone = $('#toClone');
	$.each(data, function(k, v) {
		var thumb = $(toclone).find('.thumbToClone').clone();
		$(thumb).attr('id','product-' + v.id);
		$(thumb).removeClass('thumbToClone').addClass('thumb');
		$(thumb).find('img').attr('src','.' + v.img).attr('alt', v.name);
		$(thumb).find('.caption').find('h4').append(v.name);
		$(thumb).find('.caption').find('p').append('<blockquote>' + v.description + '</blockquote>');
		$(thumb).find('.caption').find('p').append('<span class="label label-default">' + v.brand + "</span>");
		$(thumb).find('.caption').find('h3').append('$' + parseFloat(v.price).toFixed(2));
		$(thumb).find('.caption').find('.addToCart').attr('id',v.id);
		$(thumb).find('.caption').find('.addToCart').attr('product-id',v.id);
		productHasCoupon(v.id);
		$('#catalogue').append(thumb);
	});
	getRules('Coupon');
}

function getRules(object){
	$.ajax({
		url: 'http://ec2-54-200-237-246.us-west-2.compute.amazonaws.com:5002/api/rule/consult',
		dataType: 'jsonp',
		async: false,
		data: {
			objects: [object]
		},
		success: function(data) {
			var d = jQuery.parseJSON(data);	
			console.log(d);
			$.each(d.rules,function(k, v) {
				if(v.state == "Connected"){
					$('#rule-notation').append('<pre>' + v.json_notation + '</pre>');
					if(v.name == "Rule 2 Modified" || v.name == "Rule 2" ){
						jQuery.cookie('rule',v.name);
					}
				}
			});
			
			//$('#rule-notation').append('<pre><code>' + d.rules[0].json_notation + '</code></pre>');
			//var d = jQuery.parseJSON(data);
			//console.log(d.rules[0].json_notation);
			//console.log(d);
			//return data;
		},
		error: function(error) {
			console.log(error);
			console.log("Something has happened, only God knows");
		}
	});
}
/*
	$.ajax({
		url: 'http://ec2-54-200-237-246.us-west-2.compute.amazonaws.com:5002/api/rule/consult',
		dataType: 'jsonp',
		data: {
			objects: ['Coupon']
		},
		success: function(data) {
			console.log(data);
		},
		error: function(error) {
			//It's a mistake that I used to know...
			console.log(error);
			console.log("Something has happened, only God knows");
		}
	});
*/