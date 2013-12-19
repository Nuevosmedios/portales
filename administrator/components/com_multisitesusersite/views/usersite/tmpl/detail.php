<?php defined('_JEXEC') or die('Restricted access'); ?>
<?php
   $lists   = &$this->lists;
   $row = &$this->row;
   $action = 'index.php?option=com_multisitesusersite';

	JPluginHelper::importPlugin('system');
	$dispatcher = &JDispatcher::getInstance();

?>
<form action="<?php echo JRoute::_( $action); ?>" method="post" name="adminForm" id="adminForm">
<?php /* ------ Detail ---- */ ?>
   <table class="adminlist" cellspacing="1">
<?php if ( !empty( $row->title)) { ?>   
   <thead>
      <tr>
         <th width="" colspan="2" >
            <?php echo $row->title; ?>
         </th>
      </tr>
   </thead>
<?php } ?>   
   <tbody>
   <tr id="tr_user_id" <?php echo $row->user_id; ?>>
      <td class="helpMenu">
         <label for="user_id">
            <strong><?php echo JText::_( 'MULTISITESUSERSITE_DETAIL_USER_ID' ); ?>:</strong>
         </label>
      </td>
      <td>
         <?php echo $lists['user_id']; ?>
      </td>
   </tr>
   <tr id="tr_site_id" <?php echo $row->site_id; ?>>
      <td class="helpMenu">
         <label for="site_id">
            <strong><?php echo JText::_( 'MULTISITESUSERSITE_DETAIL_SITE_ID' ); ?>:</strong>
         </label>
      </td>
      <td>
         <?php echo $lists['site_id']; ?>
      </td>
   </tr>

<?php $dispatcher->trigger('onViewUserSite_TmplDetail', array( &$this)); ?>

   <tr id="tr_home" <?php echo $row->home; ?>>
      <td class="helpMenu">
         <label for="home">
            <strong><?php echo JText::_( 'MULTISITESUSERSITE_DETAIL_HOME' ); ?>:</strong>
         </label>
      </td>
      <td>
<?php
	   $opt = array();
		$opt[] = JHTML::_( 'select.option', '1', JText::_( 'Yes'));
		$opt[] = JHTML::_( 'select.option', '0', JText::_( 'No'));
		$radio = JHTML::_( 'select.radiolist',  $opt, "home",
		                   'class="inputbox"', 
		                   'value', 'text', 
		                   $row->home,
		                   "home"
		                 );

      echo $radio;
?>
      </td>
   </tr>

   <tr id="tr_id">
      <td class="helpMenu">
         <label for="id">
            <strong><?php echo JText::_( 'MULTISITESUSERSITE_DETAIL_ID' ); ?>:</strong>
         </label>
      </td>
      <td>
         <?php echo $row->id; ?>
         <input type="hidden" name="id"    value="<?php echo $row->id; ?>" />
      </td>
   </tr>
   
   </tbody>
   </table>
     <?php echo JHTML::_( 'form.token' ); ?>

   <input type="hidden" name="task"                value="listvideo" />
   <input type="hidden" name="checked_out"         value="<?php echo $row->checked_out; ?>" />
   <input type="hidden" name="checked_out_time"    value="<?php echo $row->checked_out_time; ?>" />
</form>