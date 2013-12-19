<script type="text/javascript" src="templates/vitaminshop/js/api.js"></script>
<script type="text/javascript">
	var $ = jQuery.noConflict();
</script>
<?php
	defined('_JEXEC') or die('Restricted access');
?>
<?php
	$user = JFactory::getUser();
?>
<div class="alert alert-success hidden" id="general-message"></div>
<div class="alert alert-warning hidden" id="store-message"></div>
<div class="" id="catalogue">
</div>
<div id="toClone">
	<div class="col-sm-5 col-md-3 thumbToClone">
		<div class="thumbnail">
			<img />
			<div class="caption">
				<h4></h4>
				<p></p>
				<h3></h3>
				<a class="btn btn-primary btn-sm addToCart" id="" data-target="#cupon"><i class="fa fa-shopping-cart"></i> Buy</a>
			</div>
		</div>
	</div>
	<div class="modal-toclone fade" id="" tabindex="-1" role="dialog" aria-labelledby="cuponLabel" aria-hidden="true">
		<div class="modal-dialog">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
					<h4 class="modal-title" id="myModalLabel">Coupon</h4>
				</div>
				<div class="modal-body">
					<dl class="dl-horizontal">
						<dt class="store-bonus">Store Bonus</dt>
						<dd class="store-bonus-apply"></dd>
						<dt class="web-bonus">Web Bonus</dt>
						<dd class="web-bonus-apply"></dd>
						<dt class="app-bonus">App Bonus</dt>
						<dd class="app-bonus-apply"></dd>
						<dt class="new-price"></dt>
						<dd class="new-price-value"></dd>
					</dl>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
					<button type="button" class="btn btn-primary buy"><i class="fa fa-shopping-cart"></i> Buy</button>
				</div>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="rule" tabindex="-1" role="dialog" aria-labelledby="ruleLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="myModalLabel">Rule notation</h4>
			</div>
			<div class="modal-body">
				<div id="rule-notation"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="paymentCompleted" tabindex="-1" role="dialog" aria-labelledby="paymentCompletedLabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="paymentCompletedLabel">Thank you for your order</h4>
			</div>
			<div class="modal-body">
				<div id="payment-data"></div>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" id="finish-order" data-dismiss="modal">Close</button>
			</div>
		</div>
	</div>
</div>

<div class="modal fade" id="paymentMethod" tabindex="-1" role="dialog" aria-labelledby="paymentMethodabel" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
				<h4 class="modal-title" id="paymentMethodLabel">Select your payment method</h4>
			</div>
			<div class="modal-body">
				<form class="form-horizontal" role="form">
					<div class="form-group">
						<div class="radio col-sm-offset-2 col-sm-10">
							<label>
								<input type="radio" name="optionsRadios" id="cash" value="cash" checked>
								Cash
							</label>
						</div>
						<div class="radio col-sm-offset-2 col-sm-10">
							<label>
								<input type="radio" name="optionsRadios" id="credit" value="credit">
								Credit
							</label>
						</div>
						<div class="radio col-sm-offset-2 col-sm-10">
							<label>
								<input type="radio" name="optionsRadios" id="debit" value="debit">
								Debit
							</label>
						</div>
						<div class="radio col-sm-offset-2 col-sm-10">
							<label>
								<input type="radio" name="optionsRadios" id="Check" value="check">
								Check
							</label>
						</div>
						<div class="radio col-sm-offset-2 col-sm-10" id="loyal-payment">
							<label>
								<input type="radio" name="optionsRadios" id="loyalty" value="loyalty">
								Loyalty points
							</label>
						</div>
					</div>
				</form>
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
				<button type="button" class="btn btn-primary apply-buy"><i class="fa fa-shopping-cart"></i> Buy</button>
			</div>
		</div>
	</div>
</div>