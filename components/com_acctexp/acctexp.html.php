<?php
/**
 * @version $Id: acctexp.html.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Main HTML Frontend
 * @copyright 2006-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

function joomlaregisterForm($option, $useractivation)
{
	global $aecConfig;

	$name = $username = $email = '';

	$values = array( 'name', 'username', 'email' );

	foreach ( $values as $n ) {
		if ( isset( $_POST[$n] ) ) {
			$$n = $_POST[$n];
		}
	}

	// used for spoof hardening
	if ( function_exists( 'josSpoofValue' ) ) {
		$validate = josSpoofValue();
	} else {
		$validate = '';
	}
	?>
	<script type="text/javascript">
		/* <![CDATA[ */
		function submitbutton_reg() {
			var form = document.mosForm;
			var r = new RegExp("[\<|\>|\"|\'|\%|\;|\(|\)|\&|\+|\-]", "i");

			// do field validation
			if (form.name.value == "") {
				alert( "<?php echo html_entity_decode(_REGWARN_NAME);?>" );
			} else if (form.username.value == "") {
				alert( "<?php echo html_entity_decode(_REGWARN_UNAME);?>" );
			} else if (r.exec(form.username.value) || form.username.value.length < 3) {
				alert( "<?php printf( html_entity_decode(_VALID_AZ09_USER), html_entity_decode(_PROMPT_UNAME), 2 );?>" );
			} else if (form.email.value == "") {
				alert( "<?php echo html_entity_decode(_REGWARN_MAIL);?>" );
			} else if (form.password.value.length < 6) {
				alert( "<?php echo html_entity_decode(_REGWARN_PASS);?>" );
			} else if (form.password2.value == "") {
				alert( "<?php echo html_entity_decode(_REGWARN_VPASS1);?>" );
			} else if ((form.password.value != "") && (form.password.value != form.password2.value)){
				alert( "<?php echo html_entity_decode(_REGWARN_VPASS2);?>" );
			} else if (r.exec(form.password.value)) {
				alert( "<?php printf( html_entity_decode(_VALID_AZ09), html_entity_decode(_REGISTER_PASS), 6 );?>" );
			} else {
				form.submit();
			}
		}
		/* ]]> */
	</script>
	<form action="<?php echo AECToolbox::deadsureURL( 'index.php?option=com_acctexp&amp;task=saveRegistration' ); ?>" method="post" name="mosForm">

	<div class="componentheading">
		<?php echo _REGISTER_TITLE; ?>
	</div>

	<table cellpadding="0" cellspacing="0" border="0" width="100%" class="contentpane">
	<tr>
		<td colspan="2"><?php echo _REGISTER_REQUIRED; ?></td>
	</tr>
	<tr>
		<td width="30%">
			<?php echo _REGISTER_NAME; ?> *
		</td>
			<td>
				<input type="text" name="name" size="40" value="<?php echo $name;  ?>" class="inputbox" maxlength="50" />
			</td>
	</tr>
	<tr>
		<td>
			<?php echo _REGISTER_UNAME; ?> *
		</td>
		<td>
			<input type="text" name="username" size="40" value="<?php echo $username;  ?>" class="inputbox" maxlength="25" />
		</td>
	</tr>
	<tr>
		<td>
			<?php echo _REGISTER_EMAIL; ?> *
		</td>
		<td>
			<input type="text" name="email" size="40" value="<?php echo $email;  ?>" class="inputbox" maxlength="100" />
		</td>
	</tr>
	<tr>
		<td>
			<?php echo _REGISTER_PASS; ?> *
		</td>
			<td>
				<input class="inputbox" type="password" name="password" size="40" value="" />
			</td>
	</tr>
	<tr>
		<td>
			<?php echo _REGISTER_VPASS; ?> *
		</td>
		<td>
			<input class="inputbox" type="password" name="password2" size="40" value="" />
		</td>
	</tr>
	<tr>
			<td colspan="2">
			</td>
	</tr>
	<?php
	if ( $aecConfig->cfg['use_recaptcha'] && !empty( $aecConfig->cfg['recaptcha_publickey'] ) ) {
		include_once( JPATH_SITE . '/components/com_acctexp/lib/recaptcha/recaptchalib.php' );
		?>
		<tr>
			<td></td>
			<td><?php echo recaptcha_get_html( $aecConfig->cfg['recaptcha_publickey'] ); ?></td>
		</tr>
		<?php
	}
	?>
	</table>
	<input type="hidden" name="id" value="0" />
	<input type="hidden" name="gid" value="0" />
	<input type="hidden" name="useractivation" value="<?php echo $useractivation;?>" />
	<input type="hidden" name="option" value="com_acctexp" />
	<input type="hidden" name="task" value="saveRegistration" />
	<input type="hidden" name="usage" value="<?php echo $_POST['usage'];?>" />
	<input type="hidden" name="processor" value="<?php echo $_POST['processor'];?>" />
	<?php if ( isset( $_POST['recurring'] ) ) { ?>
	<input type="hidden" name="recurring" value="<?php echo $_POST['recurring'];?>" />
	<?php } ?>
	<input type="button" value="<?php echo _BUTTON_SEND_REG; ?>" class="button" onClick="submitbutton_reg()" />
	<input type="hidden" name="<?php echo $validate; ?>" value="1" />
	</form>
	<?php
}
?>
