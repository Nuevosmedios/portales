<?php
/**
 * @version $Id: mi_googleanalytics.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Micro Integrations - Google Analytics
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class mi_googleanalytics
{
	function Info()
	{
		$info = array();
		$info['name'] = JText::_('AEC_MI_NAME_GOOGLEANALYTICS');
		$info['desc'] = JText::_('AEC_MI_DESC_GOOGLEANALYTICS');
		$info['type'] = array( 'tracking.analytics', 'vendor.google' );

		return $info;
	}

	function Settings()
	{
		$settings = array();
		$settings['account_id']			= array( 'inputB' );
		$settings['method'] 			= array( 'list' );

		$listsValues[] = JHTML::_('select.option', "3", "Asynchronous Tracking" );
		$listsValues[] = JHTML::_('select.option', "2", "Standard Trackingg" );
		$listsValues[] = JHTML::_('select.option', "1", "Old Urchin Tracking" );

		$settings['lists']['method']	= JHTML::_('select.genericlist', $listsValues, 'method', 'size="1"', 'value', 'text', empty( $this->settings['method'] ) ? '2' : $this->settings['method'] );

		return $settings;
	}

	function CommonData()
	{
		return array( 'account_id', 'method' );
	}

	function action( $request )
	{
		$db = &JFactory::getDBO();

		$app = JFactory::getApplication();

		switch ( $this->settings['ga_method'] ) {
			case 1:
				// Old Urchin Tracking Method
				$text = '<script src="http://www.google-analytics.com/urchin.js" type="text/javascript">'
							. '</script>'
							. '<script type="text/javascript">'
							. '  _uacct="' . $this->settings['account_id'] . '";'
							. '  urchinTracker();'
							. '</script>'
							. '<form style="display:none;" name="utmform">'
							. '<textarea id="utmtrans">UTM:T|' . $request->invoice->invoice_number . '|' . $app->getCfg( 'sitename' ) . '|' . $request->invoice->amount . '|0.00|0.00|||'
							. 'UTM:I|' . $request->invoice->invoice_number . '|' . $request->plan->id . '|' . $request->plan->name . '|subscription|' . $request->invoice->amount . '|1</textarea>'
							. '</form>'
							. '<script type="text/javascript">'
							. '__utmSetTrans();'
							. '</script>';
				break;
			case 2:
				// New Standard Tracking Method
				$text = '<script type="text/javascript">' . "\n"
						. '	/* <![CDATA[ */' . "\n"
						. 'var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");' . "\n"
						. 'document.write(unescape("%3Cscript src=\'" + gaJsHost + "google-analytics.com/ga.js\' type=\'text/javascript\'%3E%3C/script%3E"));' . "\n"
						. '	/* ]]> */' . "\n"
						. '</script>' . "\n"
						. '<script type="text/javascript">' . "\n"
						. '	/* <![CDATA[ */' . "\n"
						. 'try {' . "\n"
						. 'var pageTracker = _gat._getTracker("' . $this->settings['account_id'] . '");' . "\n"
						. 'pageTracker._addTrans('
						// Order ID, Affiliation, Total, Tax, Shipping, City, State, Country
						. '"' . $request->invoice->invoice_number . '",'
						. '"' . $app->getCfg( 'sitename' ) . '",'
						. '"' . $request->invoice->amount . '",'
						. '"0",'
						. '"0",'
						. '"",'
						. '"",'
						. '""'
						. ');' . "\n"
						. 'pageTracker._addItem('
						// Order ID, SKU, Product Name, Price, Quantity
						. '"' . $request->invoice->invoice_number . '",'
						. '"' . $request->plan->id . '",'
						. '"' . $request->plan->name . '",'
						. '"' . $request->invoice->amount . '",'
						. '"1"'
						. ');' . "\n"
						. 'pageTracker._trackTrans();' . "\n"
						. '} catch(err) {}' . "\n"
						. '	/* ]]> */' . "\n"
						. '</script>' . "\n"
						;
				break;
			case 3:
			default:
				// New Asynchronous Tracking Method
				$text =  '<script type="text/javascript">' . "\n"
						. '	/* <![CDATA[ */' . "\n"
						. 'var _gaq = _gaq || [];' . "\n"
						. '_gaq.push(["_setAccount", "' . $this->settings['account_id'] . '"]);' . "\n"
						. '_gaq.push(["_trackPageview"]);' . "\n"
						. '_gaq.push(["_addTrans",'
						// Order ID, Affiliation, Total, Tax, Shipping, City, State, Country
						. '"' . $request->invoice->invoice_number . '",'
						. '"' . $app->getCfg( 'sitename' ) . '",'
						. '"' . $request->invoice->amount . '",'
						. '"0",'
						. '"0",'
						. '"",'
						. '"",'
						. '""'
						. ']);' . "\n"
						. '_gaq.push(["_addItem",'
						// Order ID, SKU, Product Name, Category, Price, Quantity
						. '"' . $request->invoice->invoice_number . '",'
						. '"' . $request->plan->id . '",'
						. '"' . $request->plan->name . '",'
						. '"Membership",'
						. '"' . $request->invoice->amount . '",'
						. '"1"'
						. ']);' . "\n"
						. '_gaq.push(["_trackTrans"]);' . "\n"
						. '(function() {' . "\n"
						. 'var ga = document.createElement("script"); ga.type = "text/javascript"; ga.async = true;' . "\n"
						. 'ga.src = ("https:" == document.location.protocol ? "https://ssl" : "http://www") + ".google-analytics.com/ga.js";' . "\n"
						. '(document.getElementsByTagName("head")[0] || document.getElementsByTagName("body")[0]).appendChild(ga);' . "\n"
						. '})();' . "\n"
						. '	/* ]]> */' . "\n"
						. '</script>' . "\n"
						;
				break;
		}

		$displaypipeline = new displayPipeline();
		$displaypipeline->create( $request->metaUser->userid, 1, 0, 0, null, 1, $text );

		return true;
	}
}
?>
