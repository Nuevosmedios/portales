<?php
/**
 * @file       multisitesdb.php
 * @version    1.2.96
 * @author     Edwin CHERONT     (e.cheront@jms2win.com)
 *             Edwin2Win sprlu   (www.jms2win.com)
 * @copyright  Joomla Multi Sites
 *             Single Joomla! 1.5.x installation using multiple configuration (One for each 'slave' sites).
 *             (C) 2008-2012 Edwin2Win sprlu - all right reserved.
 * @license    This program is free software; you can redistribute it and/or
 *             modify it under the terms of the GNU General Public License
 *             as published by the Free Software Foundation; either version 2
 *             of the License, or (at your option) any later version.
 *             This program is distributed in the hope that it will be useful,
 *             but WITHOUT ANY WARRANTY; without even the implied warranty of
 *             MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *             GNU General Public License for more details.
 *             You should have received a copy of the GNU General Public License
 *             along with this program; if not, write to the Free Software
 *             Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 *             A full text version of the GNU GPL version 2 can be found in the LICENSE.php file.
 * @par History:
 * - V1.1.0 02-OCT-2008: Initial version
 * - V1.1.2 29-NOV-2008: Fix bug when deleting DB tables. 
 *                       The character '_' must be escaped because it is interpreted like a single wildcard
 * - V1.1.3 05-DEC-2008: Add checking if the image and media folder can be replicated successfully.
 *                       Otherwise, report an error.
 *                       Also use the master image and media folder as "from" directory when they does not
 *                       existing in the "from" slave directory.
 * - V1.1.4 09-DEC-2008: Fix bug when copy DB tables related to "_" character. 
 *                       The character '_' must be escaped because it is interpreted like a single wildcard
 * - V1.1.11 06-JAN-2008: Fix problem to save the new admin password and email.
 *                        The problem was due to the fact that the current user has not enough permission to
 *                        update the super admin or admin information.
 *                        I have credential the current user to Super Administrator just the time to write the info. 
 * - V1.2.00 03-MAY-2009: Add sharing tables using the SQL VIEW.
 * - V1.2.00 RC2 21-JUN-2009 : Review DB User name and password sanitisation.
 *                             Special character are now: '_.,;:=-+/*@#$£!&(){}[]<>§'
 *                             Fix also a problem when installing components and synchronizing the menu.
 *                             The word "option" is reserved. So it needs to be preceeded with the alias.
 * - V1.2.03 14-AUG-2009 : Add a check that a DB already exists before trywing make a new DB.
 *                         It was assumed that when making a DB, it was built on the same server.
 *                         Check that the "to DB" already exists allow to replicate a website
 *                         into a DB located in another server.
 * - V1.2.13 18-NOV-2009 : Add the {reset} keyword processing.
 * - V1.2.14 25-NOV-2009 : Verify that sourceDB and targetDB are present in the copyDBSharing in case
 *                         where it was not possible to retreive the DB configuration.
 *                         Also add a check that we are not in safe mode to modify the set_time_limit
 * - V1.2.20 24-JAN-2010 : Check that NewAdminPsw and NewAdminEmail are not empty in case where they
 *                         are not entered in the front-end
 *                         (Possibility to make them facultative when creating front-end with shared users).
 *                         Fix the test to used the ROOT login and password.
 *                         (Use them when they are present and not the reverse)
 * - V1.2.24 17-MAR-2010 : Add cross-check when retreiving "FROM table name" present a view statement.
 *                         Sometimes, the "from" table name is not the last word of a VIEW.
 *                         It maybe finished with something else like
 *                         ..... from `dbname`.`tablename` WITH CASCADED CHECK OPTION'
 * - V1.2.29 17-MAY-2010 : Add the keywords {user_login} and {user_name} that could be used to compute field values.
 * - V1.2.30 01-JUN-2010 : Add the keywords {site_id_letters} and call onKeywordResolution plugin function to allow customized keywords.
 * - V1.2.33 08-JUL-2010 : Add the possibility to exclude table in the sharing.
 *                         Add compatibility with Joomla 1.6 beta 5
 * - V1.2.43 10-NOV-2010 : Add the keywords {user_email} that could be used to compute field values.
 * - V1.2.44 03-DEC-2010 : Add the Joomla 1.6 compatibility for the installation of extensions
 *                         This allow using the JMS Tool "install/share/uninstall" actions on component, modules and plugins
 *                         This also allow refreshing a slave site once it is created and that new extensions are installed in the parent
 *                         website.
 * - V1.2.47 27-JAN-2011 : Add verification that the "to DB" exists when replicating a slave site.
 *                         This additional verification is perform by a check if the DBO has a valid "resource"
 *                         created on the DB.
 *                         When there is no valid DB "resource", try creating the DB.
 * - V1.2.51 22-APR-2011 : Add cleanup of the "from" view parameter in case where it reads a view created by another component.
 *                         Some extension like OSE Membership also create views with parenthesis.
 *                         Add processing of template new admin email address.
 *                         Fix a warning in the DB verification.
 * - V1.2.56 05-JUL-2011 : Add {site_id-n} keywords computed based on {site_id} value.
 * - V1.2.58 18-JUL-2011 : Add a check that the "from DB" exists when replicating a website.
 *                         This can avoid a fatal error in case where the DB connection failed.
 * - V1.2.61 02-AUG-2011 : Add Joomla 1.6 & 1.7 compatibility to create DB dynamically.
 *                         Also fix the "media setting" for the images directory path.
 *                         j1.5        = /images/stories
 *                         j1.6 or 1.7 = /images
 * - V1.2.62 02-AUG-2011 : resolve the "Joomla table prefix" in the "is_view".
 * - V1.2.64 07-SEP-2011 : Remove "static" function to maintain compatibility with PHP 4.3.
 * - V1.3.00 16-AUG-2011 : Add possibility to customize the "/multisites" directory path
 * - V1.2.65 18-SEP-2011: - Add possibility to create complex view with a WHERE clause.
 *                        - Add the processing of additional queries that could be attached to the creation of a view
 * - V1.2.66 04-OCT-2011: - Force recheck of the DB connection with the "fromDBUser or rootDBUser" when the connection fail.
 *                          This allow later perform a dynamic DB Creation when the "toDBUser" does not exists or has not enought privileges.
 * - V1.2.67 08-OCT-2011: - When there is a complex view to create with a "tablewhere" clause then try computing the original physical DB name and table name
 *                          by a parsing of the existing VIEW rule to extract the DB Name and DB Table name.
 *                        - Fix joomla 1.6, 1.7 compatibility when working with sever MySQL users.
 *                          The "connection" parameter can not be accessed due to "protected" field.
 *                          So if ( empty( $db-$con)) always fails.
 * - V1.2.69 03-NOV-2011: - Fix the computation of the complex view when there is a "from" word present in the view to replicate.
 *                          Now search for the " FROM " (with space) to avoid confusion with field 'fromname', 'fromemail' used by acymailing.
 * - V1.2.77 19-FEB-2012: - Fix the update of the Admin User info for joomla 1.7 & 2.5 to avoid updating the current websites.
 * - V1.2.80 19-MAR-2012: - Add several keywords {rnd_alnum}, {rnd_prefix}, {user_email_left}, {user_email_left_alnum, {user_email_right}, {user_email_right-X}, {autoinc}
 * - V1.2.81 20-MAR-2012: - Add several keywords {site_id_left}, {site_id_left_alnum}, {site_id_right}, {site_id_right-X},
 *                          {new_admin_email}, {new_admin_left}, {new_admin_left_alnum}, {new_admin_right}, {new_admin_right-X},
 * - V1.2.83 03-APR-2012: - Restore the {site_id-n} that were removed accidentally when adding keywords
 * - V1.2.85 07-APR-2012: - Create a "Light DB" API that is used to create the "TO" User and DB.
 *                          This avoid compatibility problems between Joomla 1.5 and 2.5 but also avoid problem with Joomla Platform
 *                          to return copy of the DB objects instead of a single instance (reference).
 * - V1.2.86 15-APR-2012: - Add possibility to cutomize the "to email address" of the notification
 *                        - Also give the possibility to force sending email notification when enabled.
 *                        - Add several keywords {site_alias_left}, {site_alias_left_alnum}, {site_alias_right}, {site_alias_right-X},
 * - V1.2.87 24-APR-2012: - Add the automatic DBName detection in isView in case where the routine is called with the joomla database object instead of a JMS one.
 *                        - Improve the getViewFrom() routine to allow returning the physical from table name without the where clause and other additional parameters.
 * - V1.2.88 22-MAY-2012: - Fix the creation of views that may ignore some filtering.
 *                        - Use the list of VIEWS created to make them ignore when creating the TABLES.
 *                          This avoid creating default views without any filtering.
 * - V1.2.92 14-JUN-2012: - Remove a "PHP notice" message due to compatibility with joomla 2.5.
 * - V1.2.95 03-OCT-2012: - Add a verification of the extension data for consistency in the menu and update of the extension manifest_cache.
 *                        - Fix the installation of new modules and plugins under Joomla 2.5.
 *                          Reset the "extension_id" to insert the new record and avoid replacing existing one.
 *                        - Add possibility to copy the tables without the joomla #__ prefix. (ie. JooCart (oc_%) or CiviCRM (civicrm_%))
 * - V1.2.96 04-OCT-2012: - Fix bug when create MySQL Users dynamically .
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die( 'Restricted access' );

@include_once( JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_multisites' .DIRECTORY_SEPARATOR. 'classes' .DIRECTORY_SEPARATOR. 'lettertree.php');

require_once( dirname( __FILE__) .DIRECTORY_SEPARATOR. 'multisitesdblight.php');



// ===========================================================
//             MultisitesDatabase class
// ===========================================================
/**
 * @brief Interface to replicate a database.
 * It also configure the database to update some configuration parameters like media or image folder path.
 */
class MultisitesDatabase extends MultisitesDatabaseLight
{
   //------------ generatePassword ---------------
   /**
    * @brief Generate a password
    */
	function generatePassword( $aPasswordLen=10)
   {
      // Don't use special characters like <=&% that could mis understood when the password in copied in a URL parameter
      $initStr = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789_.,;:=-+*/@#$£!(){}[]<>§';
      
      // Melange la chaine InitStr avant son utilisation
   	mt_srand((double)microtime()*1000000);
      $rndStr = "";
      for( ; strlen($initStr)>0; )
      {
         $len = strlen( $initStr);
         $iPos = mt_rand( 0, $len-1);
         $rndStr .= substr( $initStr, $iPos, 1);
         // If first character
         if ($iPos == 0) {
            $initStr = substr( $initStr, 1);
         }
         // If last character
         else if ( $iPos == ($len - 1)) {
            $initStr = substr( $initStr, 0, $iPos);
         }
         // If middle
         else {
            $initStr = substr( $initStr, 0, $iPos) . substr( $initStr, $iPos+1);
         }
      }
      
      
      // Choisir n caractères pour constituer le mot de passe
      $len = strlen( $rndStr);
      $psw = "";
      for ( $i=0; $i<$aPasswordLen; $i++) {
         $iPos = mt_rand( 0, $len-1);
         $psw .= substr( $rndStr, $iPos, 1);
      }
      return $psw;
    }

   //------------ generatePassword ---------------
   /**
    * @brief Generate a password
    */
	function generatePasswordAlphaNumeric( $aPasswordLen=10)
   {
      // Use only Alpha Numeric characters with Lower Case and Upper Case
      $initStr = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
      
      // Melange la chaine InitStr avant son utilisation
   	mt_srand((double)microtime()*1000000);
      $rndStr = "";
      for( ; strlen($initStr)>0; )
      {
         $len = strlen( $initStr);
         $iPos = mt_rand( 0, $len-1);
         $rndStr .= substr( $initStr, $iPos, 1);
         // If first character
         if ($iPos == 0) {
            $initStr = substr( $initStr, 1);
         }
         // If last character
         else if ( $iPos == ($len - 1)) {
            $initStr = substr( $initStr, 0, $iPos);
         }
         // If middle
         else {
            $initStr = substr( $initStr, 0, $iPos) . substr( $initStr, $iPos+1);
         }
      }
      
      
      // Choisir n caractères pour constituer le mot de passe
      $len = strlen( $rndStr);
      $psw = "";
      for ( $i=0; $i<$aPasswordLen; $i++) {
         $iPos = mt_rand( 0, $len-1);
         $psw .= substr( $rndStr, $iPos, 1);
      }
      return $psw;
    }


   //------------ generateRndWord ---------------
   /**
    * @brief Generate a random prefix.
    * This is a random alpha numeric value in UpperCase and lowercase.
    */
	function generateRndWord( $aLen=10)
   {
		// Create the random prefix:
		$prefix  = '';
		$chars   = range('a', 'z');
		$charsUC = range('A', 'Z');

		// Next we combine the numbers and characters to get the other characters:
		$symbols = array_merge($chars, $charsUC);
		shuffle($symbols);

		for($i = 0; $i < $aLen; $i++) {
			$prefix .= $symbols[$i];
		}

      return $prefix;
    }

   //------------ generateRndPrefix ---------------
   /**
    * @brief Generate a random prefix.
    * This is a random alpha numeric value in lower case with first character that is a letter.
    */
	function generateRndPrefix( $aPrefixLen=10)
   {
		// Create the random prefix:
		$prefix  = '';
		$chars   = range('a', 'z');
		$numbers = range(0, 9);

		// We want the fist character to be a random letter:
		shuffle($chars);
		$prefix .= $chars[0];

		// Next we combine the numbers and characters to get the other characters:
		$symbols = array_merge($numbers, $chars);
		shuffle($symbols);

		for($i = 1; $i < $aPrefixLen; $i++) {
			$prefix .= $symbols[$i];
		}

      return $prefix;
    }

   //------------ generateAutoInc ---------------
   /**
    * @brief Generate a number with auto-incrementation when the {autoinc} or {autoinc+offset} pattern is present.
    */
	function generateAutoInc()
   {
		static $instance;
		
		// If an increment is already computed, re-use it so that the {autoinc} will have the same value during the transaction.
		if ( isset( $instance)) {
		   return $instance;
		}
		
      // Compute the location where the auto-increment file is located.
      if (defined( 'MULTISITES_AUTOINC_DIR'))   { $autoinc_dir = MULTISITES_AUTOINC_DIR; }
      else                                      { $autoinc_dir = dirname( __FILE__); }

      // Compute the filename where plenty of dot(.) are written to represent the value of the counter.
	   $filename = $autoinc_dir .DS. 'autoinc.dat';
	   $fp = fopen( $filename, 'a');
	   if ( $fp != false) {
	      fseek( $fp, 0, SEEK_END);
	      fputs( $fp, '.');
	      $instance = ftell( $fp);
   	   fclose( $fp);
         return $instance;
	   }
	   // Case of file error
	   return -1;
    }
    
   //------------ _evalStr ---------------
   /**
    * Convert a set of keyword with their corresponding values
    */
   function evalStr( $str, $site_id, $site_dir, $deploy_dir, $dbInfo)
	{
	   // If there is a at "@" present in the site ID
	   $pos = strpos( $site_id, '@');
	   if ( $pos === false) {
	      $site_id_left = $site_id_left_alnum = '';
	      $site_id_right_search = $site_id_right_replace = $site_id_right = '';
	   }
	   else {
   	   list( $site_id_left, $site_id_right) = explode( '@', $site_id);
   		$site_id_left_alnum  = preg_replace('/[^A-Za-z0-9\-]/i', '', $site_id_left);
   
   		// ------ Compute the {user_email_right-n} keywords ----
   		$site_id_right_search  = array();
   		$site_id_right_replace = array_reverse( explode( '.', $site_id_right));
   		if ( !empty($site_id_right_replace) && is_array( $site_id_right_replace)) {
      		for ( $i=0; $i<count($site_id_right_replace); $i++) {
      		   $site_id_right_search[$i] = '{site_id_right-'.($i+1).'}';
      		}
      	}
      }
      
      $user    = JFactory::getUser();
	   $user_id = $user->id;
	   $user_login = $user->username;
	   $user_name  = $user->name;
	   $user_email = $user->email;

	   list( $user_email_left, $user_email_right) = explode( '@', $user_email);
		$user_email_left_alnum  = preg_replace('/[^A-Za-z0-9\-]/i', '', $user_email_left);

		// ------ Compute the {user_email_right-n} keywords ----
		$user_email_right_search  = array();
		$user_email_right_replace = array_reverse( explode( '.', $user_email_right));
		if ( !empty($user_email_right_replace) && is_array( $user_email_right_replace)) {
   		for ( $i=0; $i<count($user_email_right_replace); $i++) {
   		   $user_email_right_search[$i] = '{user_email_right-'.($i+1).'}';
   		}
   	}

	   if ( empty( $deploy_dir)) {
	      $deploy_dir = $site_dir;
	   }
	   
      $site_id_letters = $site_id;
      if ( class_exists( 'MultisitesLetterTree')) {
         // Try to compute a path using the letter tree
         $lettertree_dir = MultisitesLetterTree::getLetterTreeDir( $site_id);
         if( !empty( $lettertree_dir)) {
            $site_id_letters = $lettertree_dir;
         }
      }
      
		// ------ Compute the {site_id-n} keywords ----
		$site_id_search  = array();
		$site_id_replace = array_reverse( explode( '.', $site_id));
		if ( !empty($site_id_replace) && is_array( $site_id_replace)) {
   		for ( $i=0; $i<count($site_id_replace); $i++) {
   		   $site_id_search[$i] = '{site_id-'.($i+1).'}';
   		}
   	}
      
	   
	   $site_prefix = (isset( $dbInfo['site_prefix']))
	                ? $dbInfo['site_prefix']
	                : '';
	   if ( empty( $dbInfo['site_alias'])) {
	      $site_alias  = '';
	      $site_alias_left = $site_alias_left_alnum = '';
   		$site_alias_right = '';
   		$site_alias_right_search  = '';
   		$site_alias_right_replace = '';
	   }
	   else {
   	   $site_alias  = $dbInfo['site_alias'];

   	   list( $site_alias_left, $site_alias_right) = explode( '@', $site_alias);
   		$site_alias_left_alnum  = preg_replace('/[^A-Za-z0-9\-]/i', '', $site_alias_left);
   
   		// ------ Compute the {site_alias_right-n} keywords ----
   		$site_alias_right_search  = array();
   		$site_alias_right_replace = array_reverse( explode( '.', $site_alias_right));
   		if ( !empty($site_alias_right_replace) && is_array( $site_alias_right_replace)) {
      		for ( $i=0; $i<count($site_alias_right_replace); $i++) {
      		   $site_alias_right_search[$i] = '{site_alias_right-'.($i+1).'}';
      		}
      	}

	   }
	   
	   $site_domain = (isset( $dbInfo['site_domain']))
	                ? $dbInfo['site_domain']
	                : '';

	   if ( empty( $dbInfo['newAdminEmail'])) {
	      $new_admin_email = '';
	      $new_admin_email_left = $new_admin_email_left_alnum = '';
   		$new_admin_email_right = '';
   		$new_admin_email_right_search  = '';
   		$new_admin_email_right_replace = '';
	   }
	   else {
   	   $new_admin_email = $dbInfo['newAdminEmail'];

   	   list( $new_admin_email_left, $new_admin_email_right) = explode( '@', $new_admin_email);
   		$new_admin_email_left_alnum  = preg_replace('/[^A-Za-z0-9\-]/i', '', $new_admin_email_left);
   
   		// ------ Compute the {user_email_right-n} keywords ----
   		$new_admin_email_right_search  = array();
   		$new_admin_email_right_replace = array_reverse( explode( '.', $new_admin_email_right));
   		if ( !empty($new_admin_email_right_replace) && is_array( $new_admin_email_right_replace)) {
      		for ( $i=0; $i<count($new_admin_email_right_replace); $i++) {
      		   $new_admin_email_right_search[$i] = '{new_admin_email_right-'.($i+1).'}';
      		}
      	}
	   }

      $jpath_master = dirname( dirname( dirname( dirname( dirname(__FILE__)))));
	   if ( defined( 'JPATH_ROOT')) {
   	   $jpath_root = JPATH_ROOT;
	   }
	   else {
   	   $jpath_root = $jpath_master;
	   }
	   
      // define( 'JPATH_MULTISITES' ) - JMS 1.3.x
      @include_once( dirname( dirname( __FILE__)) .DIRECTORY_SEPARATOR. 'multisites_path.cfg.php');
	   $jpath_multisites = defined( 'JPATH_MULTISITES')
	                     ? JPATH_MULTISITES
	                     : $jpath_root .DIRECTORY_SEPARATOR.'multisites';
      
      $jroot_len = strlen( $jpath_root);
      if ( strncmp( $site_dir, $jpath_root, $jroot_len) == 0) {
         $rel_site_dir = substr( $site_dir, $jroot_len+1);
      }
      else {
         $rel_site_dir = $site_dir;
      }

		// {site_url}
		$uri =& JURI::getInstance(JURI::base());
		$url_path = rtrim( $uri->toString( array('path')) , '/\\');
		// If we are in the administration
		if ( JPATH_BASE == JPATH_ADMINISTRATOR 
		  && substr($url_path, -14)  == '/administrator'
		   ) 
		{
		   // Remove the traling "/administrator"
		   $url_path = substr( $url_path, 0, strlen( $url_path) - 14);
		}
		$site_url   = $uri->toString( array( 'host', 'port') )
		            . rtrim($url_path, '/\\');

		// {rnd_psw}
		$rnd_psw_6  = MultisitesDatabase::generatePassword( 6);
		$rnd_psw_7  = MultisitesDatabase::generatePassword( 7);
		$rnd_psw_8  = MultisitesDatabase::generatePassword( 8);
		$rnd_psw_9  = MultisitesDatabase::generatePassword( 9);
		$rnd_psw_10 = MultisitesDatabase::generatePassword( 10);
		$rnd_psw    = $rnd_psw_8;

		// {rnd_alnum}
		$rnd_alnum_6  = MultisitesDatabase::generatePasswordAlphaNumeric( 6);
		$rnd_alnum_7  = MultisitesDatabase::generatePasswordAlphaNumeric( 7);
		$rnd_alnum_8  = MultisitesDatabase::generatePasswordAlphaNumeric( 8);
		$rnd_alnum_9  = MultisitesDatabase::generatePasswordAlphaNumeric( 9);
		$rnd_alnum_10 = MultisitesDatabase::generatePasswordAlphaNumeric( 10);
		$rnd_alnum    = $rnd_alnum_8;

		// {rnd_word}
		$rnd_word_6  = MultisitesDatabase::generateRndWord( 6);
		$rnd_word_7  = MultisitesDatabase::generateRndWord( 7);
		$rnd_word_8  = MultisitesDatabase::generateRndWord( 8);
		$rnd_word_9  = MultisitesDatabase::generateRndWord( 9);
		$rnd_word_10 = MultisitesDatabase::generateRndWord( 10);
		$rnd_word    = $rnd_word_8;

		// {rnd_prefix}
		$rnd_prefix_6  = MultisitesDatabase::generateRndPrefix( 6);
		$rnd_prefix_7  = MultisitesDatabase::generateRndPrefix( 7);
		$rnd_prefix_8  = MultisitesDatabase::generateRndPrefix( 8);
		$rnd_prefix_9  = MultisitesDatabase::generateRndPrefix( 9);
		$rnd_prefix_10 = MultisitesDatabase::generateRndPrefix( 10);
		$rnd_prefix    = $rnd_prefix_8;

		// {autoinc}
		// If auto-inc is not present, do not increment.
		if ( preg_match_all('#{autoinc(\+([0-9]+))?}#', $str, $matches)) {
   		$autoinc_search  = array();
   		$autoinc_replace = array();
		   $autoinc = MultisitesDatabase::generateAutoInc();
   		// For each pattern found $matches[0]
		   foreach( $matches[0] as $key=>$value) {
   		   $offset            = $matches[2][$key];
      		$autoinc_search[]  = $value;
      		$autoinc_replace[] = $offset + $autoinc;
   		}
		}
		else {
         $autoinc_search  = '{autoinc}';
         $autoinc_replace = '';
		}

	   $res = $str;
	   $res = str_replace( '{deploy_dir}',       $deploy_dir,         $res);
	   $res = str_replace( '{multisites}',       $jpath_multisites,   $res);
	   $res = str_replace( '{JPATH_MULTISITES}', $jpath_multisites,   $res);
	   $res = str_replace( '{rel_site_dir}',     $rel_site_dir,       $res);
	   $res = str_replace( '{master}',           $jpath_master,       $res);
	   $res = str_replace( '{root}',             $jpath_root,         $res);
	   $res = str_replace( '{JPATH_ROOT}',       $jpath_root,         $res);
	   $res = str_replace( '{site_alias}',       $site_alias,         $res);
	   $res = str_replace( array( '{site_alias_left}', '{site_alias_left_alnum}', '{site_alias_right}'),
	                       array( $site_alias_left, $site_alias_left_alnum, $site_alias_right),
	                       $res);
	   if ( !empty( $site_alias_right_search)) {
   	   $res = str_replace( $site_alias_right_search, $site_alias_right_replace, $res);
   	}
	   $res = str_replace( '{site_dir}',         $site_dir,           $res);
	   $res = str_replace( '{site_domain}',      $site_domain,        $res);
	   $res = str_replace( '{site_id}',          $site_id,            $res);
	   $res = str_replace( $site_id_search,      $site_id_replace,    $res);
	   $res = str_replace( '{site_id_left}',     $site_id_left,       $res);
	   $res = str_replace( '{site_id_left_alnum}',$site_id_left_alnum,$res);
	   $res = str_replace( '{site_id_right}',    $site_id_right,       $res);
	   if ( !empty( $site_id_right_search)) {
	      $res = str_replace( $site_id_right_search, $site_id_right_replace, $res);
	   }
	   $res = str_replace( '{site_id_letters}',  $site_id_letters,    $res);
	   $res = str_replace( '{site_prefix}',      $site_prefix,        $res);
	   $res = str_replace( '{site_url}',         $site_url,           $res);
	   $res = str_replace( '{user_id}',          $user_id,            $res);
	   $res = str_replace( '{user_login}',       $user_login,         $res);
	   $res = str_replace( '{user_name}',        $user_name,          $res);
	   $res = str_replace( '{user_email}',       $user_email,         $res);
	   $res = str_replace( array( '{user_email_left}', '{user_email_left_alnum}', '{user_email_right}'),
	                       array( $user_email_left, $user_email_left_alnum, $user_email_right),
	                       $res);
	   $res = str_replace( $user_email_right_search, $user_email_right_replace, $res);

	   $res = str_replace( '{new_admin_email}',  $new_admin_email,    $res);
	   $res = str_replace( array( '{new_admin_email_left}', '{new_admin_email_left_alnum}', '{new_admin_email_right}'),
	                       array( $new_admin_email_left, $new_admin_email_left_alnum, $new_admin_email_right),
	                       $res);
	   if ( !empty( $new_admin_email_right_search)) {
   	   $res = str_replace( $new_admin_email_right_search, $new_admin_email_right_replace, $res);
	   }
	                       
//	   $res = str_replace( '{user_psw}',         $user_psw,           $res);
	   $res = str_replace( '{rnd_psw_6}',        $rnd_psw_6,          $res);
	   $res = str_replace( '{rnd_psw_7}',        $rnd_psw_7,          $res);
	   $res = str_replace( '{rnd_psw_8}',        $rnd_psw_8,          $res);
	   $res = str_replace( '{rnd_psw_9}',        $rnd_psw_9,          $res);
	   $res = str_replace( '{rnd_psw_10}',       $rnd_psw_10,         $res);
	   $res = str_replace( '{rnd_psw}',          $rnd_psw,            $res);
	   $res = str_replace( array( '{rnd_alnum_6}', '{rnd_alnum_7}', '{rnd_alnum_8}', '{rnd_alnum_9}', '{rnd_alnum_10}', '{rnd_alnum}'),
	                       array( $rnd_alnum_6, $rnd_alnum_7, $rnd_alnum_8, $rnd_alnum_9, $rnd_alnum_10, $rnd_alnum),
	                       $res);
	   $res = str_replace( array( '{rnd_word_6}', '{rnd_word_7}', '{rnd_word_8}', '{rnd_word_9}', '{rnd_word_10}', '{rnd_word}'),
	                       array( $rnd_word_6, $rnd_word_7, $rnd_word_8, $rnd_word_9, $rnd_word_10, $rnd_word),
	                       $res);
	   $res = str_replace( array( '{rnd_prefix_6}', '{rnd_prefix_7}', '{rnd_prefix_8}', '{rnd_prefix_9}', '{rnd_prefix_10}', '{rnd_prefix}'),
	                       array( $rnd_prefix_6, $rnd_prefix_7, $rnd_prefix_8, $rnd_prefix_9, $rnd_prefix_10, $rnd_prefix),
	                       $res);
	   $res = str_replace( $autoinc_search,      $autoinc_replace,    $res);
	   $res = str_replace( '{reset}',            '',                  $res);


      JPluginHelper::importPlugin('multisites');
		$mainframe	= &JFactory::getApplication();
      $results = $mainframe->triggerEvent('onKeywordResolution', array ( $str, & $res, $site_id, $site_dir, $deploy_dir, $dbInfo, $jpath_multisites, $site_url));
	   
	   return $res;
	}


   //------------ deleteDBTables ---------------
   /**
    * @brief Delete all the tables corresponding to the prefix defined in the DB
    */
   function deleteDBTables( &$db)
   {
      $dbprefix = str_replace('_' , '\_', $db->getPrefix());
         
//      Debug2Win::debug_start( '>> deleteDBTables() - START');
// Debug2Win::debug( 'Delete DB =' . var_export( $db, true) );
      
      // Retreive all the table with the prefix
      $db->setQuery( 'SHOW TABLES LIKE \''.$dbprefix.'%\'' );
      $tables = $db->loadResultArray();

// Debug2Win::debug( 'Delete tables =' . var_export( $tables, true) );
      if ( !empty( $tables)) {
         foreach($tables as $table)
         {
            $query = "DROP TABLE IF EXISTS $table;";
            $db->setQuery($query);
            $db->query();
         }
      }
      
      // Retreive all the table with the prefix
      $db->setQuery( 'SHOW TABLES LIKE \''.$dbprefix.'%\'' );
      $tables = $db->loadResultArray();

// Debug2Win::debug( 'Delete Views =' . var_export( $tables, true) );
      if ( !empty( $tables)) {
         foreach($tables as $table)
         {
            $query = "DROP VIEW IF EXISTS $table;";
            $db->setQuery($query);
            $db->query();
         }
      }

//      Debug2Win::debug_stop( '<< getConfigPath() - STOP');
      return true;
   }
   
   //------------ copyDbTablesIntoPrefix ---------------
   /**
    * Duplicate in the SAME database all the table with "fromPrefix" into "toPrefix".
    */
   function copyDbTablesIntoPrefix( &$db, $toPrefix, $tableNames, $structureOnly = true, $dropIfExists = false, $copyDataOnlyIfNotExists = true)
   {
      $errors = array();
      $fromPrefix = $db->getPrefix();
      
      // For each table to copy
      foreach( $tableNames as $tableName)
      {
         // Check if the table already exists
         $like = str_replace('_' , '\_', $toPrefix.$tableName);
         $query = "SHOW TABLES LIKE '$like'";
         $db->setQuery($query);
         if ($db->loadResult() == null) {
            $alreadyExists = false;
         }
         else {
            $alreadyExists = true;
         }

         // If the table exist and want to add a DROP statement
         if ($alreadyExists && $dropIfExists) {
            $query = "DROP TABLE IF EXISTS $tableName;";
            $db->setQuery($query);
            $db->query();
         }

         // Create the new table (if not already exists)
         $query = "CREATE TABLE IF NOT EXISTS "
                . "$toPrefix$tableName LIKE $fromPrefix$tableName";
         $db->setQuery( $query );
         $result = $db->query();
         if ( !$result) {
            $msg = "Error query [$query]. DB Error: " . $db->getErrorMsg();
            $errors[] = $msg;
            continue;
         }

         // If request to only copy the table structure.
         $doReplace = true;
         if ($structureOnly) {
            //echo "structure only, so not replacing!<br>".
            $doReplace = false;
         }
         // OR if the table was already present and we don't want to replace its content
         else if ($alreadyExists && $copyDataOnlyIfNotExists) {
            //echo "table already there! but copyDataOnlyIfNotExists is true...<br>".
            $doReplace = false;
         }	
      
         // Now REPLACE the content of the "to" table by the content of the "from" table
         if ($doReplace) {
            // Use the REPLACE statement instead of INSERT in aim to insert,delete,update the existing records
            $query = "REPLACE into $toPrefix$tableName SELECT * FROM $fromPrefix$tableName;";
            $db->setQuery($query);
            if (!$db->query())
            {
               $msg = "Error query [$query]. DB Error: " . $db->getErrorMsg() . " Result = " . var_export($result, TRUE);
               $errors[] = $msg;
            }
         }
      }
      
      return $errors;
   }   

   //------------ backquote ---------------
   /**
    * Adds backquotes on both sides of a database, table or field name.
    * and escapes backquotes inside the name with another backquote
    *
    * example:
    * <code>
    * echo backquote('owner`s db'); // `owner``s db`
    *
    * </code>
    *
    * @uses    backquote()
    * @uses    is_array()
    * @uses    strlen()
    * @uses    str_replace()
    * @param   mixed    $a_name    the database, table or field name to "backquote"
    *                              or array of it
    * @param   boolean  $do_it     a flag to bypass this function (used by dump
    *                              functions)
    * @return  mixed    the "backquoted" database, table or field name if the
    *                   current MySQL release is >= 3.23.6, the original one
    *                   else
    * @access  public
    */
   function backquote($a_name, $do_it = true)
   {
       if (! $do_it) {
           return $a_name;
       }
   
       if (is_array($a_name)) {
            $result = array();
            foreach ($a_name as $key => $val) {
                $result[$key] = MultisitesDatabase::backquote($val);
            }
            return $result;
       }
   
       // '0' is also empty for php :-(
       if (strlen($a_name) && $a_name !== '*') {
           return '`' . str_replace('`', '``', $a_name) . '`';
       } else {
           return $a_name;
       }
   }
   
   //------------ _isViewSupported ---------------
   /**
    * @brief Check if the DB support the creation of views.
    * MySQL < 5.0 does not support the CREATE VIEW
    */
   function _isViewSupported( &$db)
   {
	   $result = false;
	   
      // Reteive the MySQL version
      $query = "SELECT Version() AS version";
      $db->setQuery( $query );
		$versStr = $db->loadResult();

      // If MySQL version >= 5.0.0 then supported
      if ( !empty( $versStr)) {
         // <version>-xxx-log
         $vers = explode( '-', $versStr);
         if ( !empty( $vers)) {
            // Version.Release.Build
            $version = $vers[0];
            $vers = explode( '.', $version);
            if ( !empty( $vers)) {
               // If Version >= 5.0.0
               if ( intval( $vers[0]) >= 5) {
                  $result = true;
               }
/*
               // If Version >= 5.1.2
               if ( intval( $vers[0]) > 5 
                 || (intval( $vers[0]) == 5 && ( (intval( $vers[1]) == 1 && intval( $vers[2]) >= 2)
                                          || (intval( $vers[1]) > 1)
                                           )
                    )
                  ) {
                  $result = true;
               }
*/               
            }
         }
      }
      
      return $result;
   }
   
   //------------ _isView ---------------
   /**
    * @brief Check if a table is a view or a real table
    */
   function _isView( &$db, $table)
   {
      // old MySQL version (<5.0): no view
      if ( !MultisitesDatabase::_isViewSupported( $db)) {
         return false;
      }
      // A more complete verification would be to check if all columns
      // from the result set are NULL except Name and Comment.
      // MySQL from 5.0.0 to 5.0.12 returns 'view',
      // from 5.0.13 returns 'VIEW'.

      if ( empty( $db->_dbname)) {
         $db->setQuery( 'SELECT DATABASE()');
         $db->_dbname = $db->loadResult();
      }
      $dbName = MultisitesDatabase::backquote($db->_dbname);
      
		// replace the table prefix pattern in case where it is present
      $table = $db->replacePrefix( (string)$table);
		
      $like = str_replace('_' , '\_', $table);
      $query = "SHOW TABLE STATUS FROM $dbName LIKE '$like'";
      $db->setQuery( $query );
      $obj = $db->loadObject();
      if ( !empty( $obj) && !empty( $obj->Comment) && strtoupper( substr($obj->Comment, 0, 4)) == 'VIEW') {
         return true;
      }
      return false;
    }
    
   //------------ getViewFrom ---------------
   /**
    * @brief Return the "FROM" value present in the create view
    *
    * The operation consists in retreiving the View definition and after in parsing the select statement to extract the FROM part.
    * In fact, this return the 'fromDB'.'fromTable'
    * @param $keepFromOnly When true, it just keep the words between the "from .... WHERE" and does not return all the content after the from
    *                      This can be helpfull to get the name of the physical table without the where clause filtering.
    */
   function getViewFrom( &$db, $table, $allowCreateView=false, $keepFromOnly=true)
   {
      $query = "SHOW CREATE VIEW $table";
      $db->setQuery( $query );
      $row = $db->loadAssoc();
      if ( !empty( $row) && !empty( $row['Create View'])) {
         $createView = $row['Create View'];
         $pos = strpos( strtoupper( $createView), 'SELECT');
         if ($pos === false) {}
         else {
            $str = substr( $createView, $pos);
            $arr = explode( ' ', $str);
            $arr_lc = explode( ' ', strtolower( $str));
            
            // Search for the first "FROM ..... WHERE"
            $fromPos    = array_search( 'from', $arr_lc);
            $arrFrom    = array_slice( $arr, $fromPos+1);
            $arrFrom_lc = array_values( array_slice( $arr_lc, $fromPos+1));

            // If complex expression (more than 1 word)
            if ( count( $arrFrom_lc) > 1) {
               // If basic select ... from ... where
               // From present only once and where also present only once
               $cntValues   = array_count_values( $arrFrom);
               if ( empty( $cntValues['from']) && $cntValues['where'] ==1 && $keepFromOnly) {
                  // Extract the part between the from and where
                  $wherePos = array_search( 'where', $arrFrom_lc);
                  $arrFrom    = array_slice( $arrFrom, 0, $wherePos);
               }
               // If complex View
               else {
                  if( $allowCreateView) {
                     // ie. case of OSE Membership that contain complex views
                     return $createView;
                  }
                  // If just keep the word between the from and where
                  else if ($keepFromOnly) {
                     $wherePos = array_search( 'where', $arrFrom_lc);
                     if ( !empty( $wherePos)) {
                        $arrFrom = array_slice( $arrFrom, 0, $wherePos);
                     }
                  }
               }
            }
            $from = implode( ' ', $arrFrom);
            
            // If still not found
            if ( empty( $from)) {
               // Then use default rule that consists in using the last word
               $from = $arr[count( $arr) -1];
            }
            return $from;
         }
      }
      return null;
   }
    
   //------------ _getViewQuery ---------------
   /**
    * @brief retreive the SELECT statment present in the view
    */
   function _getViewQuery( &$db, $table, $allowCreateView=false)
   {
      $from = MultisitesDatabase::getViewFrom( $db, $table, $allowCreateView);
      if ( !empty($from)) {
         // If the "from" already contain the full CREATE VIEW statement, return it otherwise, create a select * from
         if ( strpos( $from, 'CREATE ALGORITHM=UNDEFINED') === false) {
            $select = "SELECT * FROM $from";
            return $select;
         }
         else {
            return $from;
         }
      }
      
      return null;
   }


   //------------ _getCreateTable ---------------
   /**
    * @brief return the "CREATE TABLE" statement that must be executed to recreate the table
    */
	function _getCreateTable( &$db, $tableName)
	{
		$query = "SHOW CREATE TABLE `#__$tableName`";
		$db->setQuery( $query);
		$db->query();
		$result = $db->loadAssocList();
		$sql = $result[0]['Create Table'];
		
		// Use the generic prefix marker instead of the full table name
		$sql = str_replace( $db->getPrefix(), '#__', $sql );

		// Replace newlines with spaces 
		$sql = str_replace( "\n", " ", $sql ) . ";\n";
		
		return $sql;
	}

   //------------ _replaceObject ---------------
   /**
    * @brief This is a copy of JDatabase::insertObject execpt it used the REPLACE instead of INSERT statement
    */
	function _replaceObject( &$db, $table, &$object, $keyName = NULL )
	{
		$fmtsql = 'REPLACE INTO '.$db->nameQuote($table).' ( %s ) VALUES ( %s ) ';
		$fields = array();
		foreach (get_object_vars( $object ) as $k => $v) {
			if (is_array($v) or is_object($v) or $v === NULL) {
				continue;
			}
			if ($k[0] == '_') { // internal field
				continue;
			}
			$fields[] = $db->nameQuote( $k );
			$values[] = $db->isQuoted( $k ) ? $db->Quote( $v ) : (int) $v;
		}
		$db->setQuery( sprintf( $fmtsql, implode( ",", $fields ) ,  implode( ",", $values ) ) );
		if (!$db->query()) {
			return false;
		}
		$id = $db->insertid();
		if ($keyName && $id) {
			$object->$keyName = $id;
		}
		return true;
	}
	
   //------------ _copyExternalTable ---------------
   /**
    * @brief Copy an "external" table.
    * This routine is called when access is denied to create directly the table from another DB.
    * In this case, we are using 2 DB connections to perform the copy.
    * This routine read the DB "create table" using the "fromDB" and recreate it into the "toDB" using the second connection.
    * It also copy the data the same way.
    * It read the data using the "fromDB" and insert the record using the "toDB"
    */
	function _copyExternalTable( &$fromdb, &$todb, $tableName)
	{
      $errors = array();
      
      // Check if the table already exists
      $toPrefix = $todb->getPrefix();
      $like = str_replace('_' , '\_', $toPrefix.$tableName);
      $query = "SHOW TABLES LIKE '$like'";
      $todb->setQuery($query);
      $result = $todb->loadResult();
      if ( empty( $result)) {
   	   // Create the table based on its definition present in the "FROM DB"
   	   $query = MultisitesDatabase::_getCreateTable( $fromdb, $tableName);
         $todb->setQuery( $query );
         $result = $todb->query();
         if ( !$result) {
            $msg = "Create external Table Error query [$query]. DB Error: " . $todb->getErrorMsg();
            $errors[] = $msg;
            return $errors;
         }
      }

      // Now copy its data
		$query = "select * from `#__$tableName`";
		$fromdb->setQuery( $query);
      $rows = $fromdb->loadObjectList();
      if ( empty( $rows)) {
         return $errors;
      }
      
      // For each records
      foreach( $rows as $row) {
			if (!MultisitesDatabase::_replaceObject( $todb, "#__$tableName", $row)) {
            $msg = "Error while copying data of the table '#__$tableName'. Stopped on record with data : " . var_export( $row, true);
            $errors[] = $msg;
            break;
			}
      }

      return $errors;
	}
    
   //------------ copyDbTablesOrViews ---------------
   /**
    * Copy All tables/View from 'fromDB' to the 'toDB'.
    *
    * When the "from table" is in reallity a view, extract the view statement and replicate it.
    */
   function copyDbTablesOrViews( &$fromdb, &$todb, $toConfig, $tableNames, $structureOnly = true, $dropIfExists = false, $copyDataOnlyIfNotExists = true)
   {
      $errors = array();
      if ( !is_object( $fromdb)) {
         $msg = '"From DB" connection does not exists';
         $errors[] = $msg;
         return $errors;
      }

      $fromDBName = MultisitesDatabase::backquote($fromdb->_dbname);
      $fromPrefix = $fromdb->getPrefix();
      $toPrefix   = $todb->getPrefix();

      $db = & $todb;
      // For each table to copy
      foreach( $tableNames as $tableName)
      {
         // Check if the table already exists
         // If table without prefix
         if ( $tableName[0] == '=') {
            $fromTableName =
            $toTableName   = substr( $tableName, 1);
            // In case where the "from" and "to" DB are the same.
            if ( $todb->_dbname == $fromdb->_dbname) {
               // Don't copy a table on itself
               continue;
            }
         }
         else {
            $fromTableName = $fromPrefix.$tableName;
            $toTableName   = $toPrefix.$tableName;
         }
         $like = str_replace('_' , '\_', $toTableName);
         $query = "SHOW TABLES LIKE '$like'";
         $db->setQuery($query);
         if ($db->loadResult() == null) {
            $alreadyExists = false;
         }
         else {
            $alreadyExists = true;
         }
         
         // If the table exist and want to add a DROP statement
         if ($alreadyExists && $dropIfExists) {
            $query = "DROP TABLE IF EXISTS $toTableName;";
            $db->setQuery($query);
            $db->query();
            
            $query = "DROP VIEW IF EXISTS $toTableName;";
            $db->setQuery($query);
            $db->query();
         }
         
         
         // If the table to copy is in fact a view
         if ( MultisitesDatabase::_isView( $fromdb, $fromTableName)) {
            // ================ DUPLICATE VIEW =========
            $select = MultisitesDatabase::_getViewQuery( $fromdb, $fromTableName, true);
            if ( !empty( $select)) {
               // If the select does NOT contain a create view, then build it.
               if ( strpos( $select, 'CREATE ALGORITHM=UNDEFINED') === false) {
                  $query = "CREATE OR REPLACE VIEW $toTableName"
                         . " AS $select"
                         ;
               }
               // Otherwise, use the create view statement.
               else {
                  // If the view already exists, ignore the creation
                  if ( MultisitesDatabase::_isView( $todb, $toPrefix.$tableName)) {
                     continue;
                  }
                  // Otherwise, try creating the same view with a new "toPrefix"
                  // $query = preg_replace( '/(.+DEFINER VIEW `)('.$fromPrefix.')(.*` AS .+)/', '$1'.$toPrefix.'$3', $select);
                  $query = str_replace( $fromPrefix, $toPrefix, $select);
               }
               $todb->setQuery( $query );
               $result = $todb->query();
               if ( !$result) {
                  $err_num = $todb->getErrorNum();
                  // If table access denied
                  if ( $err_num == 1142 
                    || $err_num == 1449 
                     )
                  {
                     $msg1 = "Table/View(1) Error [$err_num] query [$query]. DB Error: " . $todb->getErrorMsg();
                     // Try grant the user on the from table
            	      $table_name = MultisitesDatabase::backquote( $fromdb->_dbname)
            	                  . '.'
            	                  . MultisitesDatabase::backquote( $fromTableName)
            	                  ;
            	      $err = MultisitesDatabase::_createUser( $fromdb, $toConfig, $table_name);
            	      // Retry the creation of the view
                     $todb->setQuery( $query );
                     $result = $todb->query();
                     if ( !$result) {
                        $errors[] = $msg1;                        // Save the original error
            			   if ( !empty( $err)) {
            			      $errors = array_merge($errors, $err);  // Also save the error on create user
            			   }
                        $msg = "Table/View Error retrying query [$query]. DB Error: " . $todb->getErrorMsg();
                        $errors[] = $msg;
                        continue;
                     }
                  }
                  else {
                     $msg = "Table/View Error [$err_num] query [$query]. DB Error: " . $todb->getErrorMsg();
                     $errors[] = $msg;
                     continue;
                  }
               }
            }
            else {
               $msg = "Unable to replicate the view from [$fromTableName]";
               $errors[] = $msg;
               continue;
            }
         }
         // If the table to copy is really a table
         else {
            // ================ COPY TABLE =========
            // If request to only copy the table structure.
            $doReplace = true;
            if ($structureOnly) {
               //echo "structure only, so not replacing!<br>".
               $doReplace = false;
            }
            // OR if the table was already present and we don't want to replace its content
            else if ($alreadyExists && $copyDataOnlyIfNotExists) {
               //echo "table already there! but copyDataOnlyIfNotExists is true...<br>".
               $doReplace = false;
            }	
            
            // Create the new table (if not already exists)
            $query = "CREATE TABLE IF NOT EXISTS "
                   . "$toTableName LIKE $fromDBName." . MultisitesDatabase::backquote( $fromTableName);
            $db->setQuery( $query );
            $result = $db->query();
            if ( !$result) {
               $err_num = $todb->getErrorNum();
               // If table access denied
               if ( $err_num == 1142 
                 || $err_num == 1449 
                  )
               {
                  // Use the 2 DB connection to perform the table copy manually 
                  // (get table description followed by create table + replicate each data manually Select / Insert)
                  $sub_errors = MultisitesDatabase::_copyExternalTable( $fromdb, $todb, $tableName, $doReplace);
      			   $errors = array_merge($errors, $sub_errors);
               }
               else {
                  $msg = "Create table error [$err_num] on query [$query]. DB Error: " . $db->getErrorMsg();
                  $errors[] = $msg;
               }
            }
            // Create table is success
            else {
               // Now REPLACE the content of the "to" table by the content of the "from" table
               if ($doReplace) {
                  // Use the REPLACE statement instead of INSERT in aim to insert,delete,update the existing records
                  $query = "REPLACE into $toTableName SELECT * FROM $fromDBName." . MultisitesDatabase::backquote( $fromTableName) . ';';
                  $db->setQuery($query);
                  if (!$db->query())
                  {
                     $msg = "Error query [$query]. DB Error: " . $db->getErrorMsg() . " Result = " . var_export($result, TRUE);
                     $errors[] = $msg;
                  }
               }
            }
         } // End table replication
      }
      
      return $errors;
   }   


   //------------  copyDbTablePatterns ---------------
   /**
    * @brief Copy tables or create views based on a list of patterns
    *
    * This retreive the list of tables corresponding to the list of patterns.
    */
   function copyDbTablePatterns( &$fromdb, &$todb, $toConfig, $tablePatterns, $structureOnly = true, $dropIfExists = false, $copyDataOnlyIfNotExists = true, $skip=NULL)
   {
      $errors = array();
      
      $srcPrefix     = $fromdb->getPrefix();
      $srcPrefix_len = strlen($srcPrefix);
      $dbprefix      = str_replace('_' , '\_', $srcPrefix);
      
      // For each patterns
      $tables = array();
      foreach( $tablePatterns as $tablePattern) {
         if ( $tablePattern == '[none]') {
            continue;
         }
         
         // Compute the real table prefix
         $str        = $fromdb->replacePrefix( $tablePattern);
         $dbprefix   = str_replace('_' , '\_', $str);

         // try retreive the list of tables that correspond to the pattern
         $fromdb->setQuery( 'SHOW TABLES LIKE \''.$dbprefix.'%\'' );
         $rows = $fromdb->loadResultArray();
         if ( empty( $rows)) {
            $errors[] = JText::_( 'SITE_DEPLOY_NO_TABLES');
         }
         else {
            // When the table pattern does not contain the prefix pattern (#__)
            if ( strpos( $tablePattern, '#__') === false) {
               // Add a fake '______=' in a such way that the '=' remains
               $fakePrefix = str_repeat( '_', $srcPrefix_len)
                           . '='
                           ;
               foreach( $rows as &$row) {
                  $row = $fakePrefix.$row;
               }
               $tables = array_merge( $tables, $rows);
            }
            else {
               $tables = array_merge( $tables, $rows);
            }
         }
      }

      // Remove the table prefix for the copy
      $tocopy = array();
      foreach($tables as $table)
      {
         // Keep the table name WITHOUT prefix
         $tablename = substr($table, $srcPrefix_len);

         // If some tables must be excluded
         if (!empty($skip) && in_array($tablename, $skip))
            continue;
         
         // Save the table name to duplicate
         $tocopy[]=$tablename;
      }
      
      if ( empty( $tocopy)) {
         $errors[] = JText::_( 'SITE_DEPLOY_TO_COPY_ERR');
      }

      // Copy the table or create views
      $errors = MultisitesDatabase::copyDbTablesOrViews( $fromdb, $todb, $toConfig,
                                                         $tocopy, $structureOnly);

   }


   //------------ dropTablePatterns ---------------
   /**
    * @brief Delete all the tables corresponding to a particular list of table patterns
    */
   function dropTablePatterns( &$db, $tablePatterns)
   {
      $errors = array();
      
      foreach( $tablePatterns as $tablePattern) {
         if ( $tablePattern == '[none]') {
            continue;
         }
         
         // Compute the real table prefix
         $str        = $db->replacePrefix( $tablePattern);
         if ( empty( $str)) {
            $msg = "Unable to get resolved the pattern [$tablePattern]. Joomla return an empty string. Check that the patch on the DB is installed.";
            $errors[] = $msg;
            continue;
         }
         $dbprefix   = str_replace('_' , '\_', $str);

         // try retreive the list of tables that correspond to the pattern
         $db->setQuery( 'SHOW TABLES LIKE \''.$dbprefix.'%\'' );
         $tables = $db->loadResultArray();
         
         // Drop the tables
         if ( !empty( $tables)) {
            foreach($tables as $table)
            {
               $query = "DROP TABLE IF EXISTS $table;";
               $db->setQuery($query);
               $db->query();
               if ( !$db->query()) {
                  $msg = "Error droping table query [$query]. DB Error: " . $db->getErrorMsg();
                  $errors[] = $msg;
               }
            }
         }
      
         // Retreive all the table with the prefix
         $db->setQuery( 'SHOW TABLES LIKE \''.$dbprefix.'%\'' );
         $tables = $db->loadResultArray();
   
         // Drop the views
         if ( !empty( $tables)) {
            foreach($tables as $table)
            {
               $query = "DROP VIEW IF EXISTS $table;";
               $db->setQuery($query);
               if ( !$db->query()) {
                  $msg = "Error droping view query [$query]. DB Error: " . $db->getErrorMsg();
                  $errors[] = $msg;
               }
            }
         }
      } // next table pattern

      return $errors;
   }

   
	/**
	 * Backward compatible Method to parse through a queries element of the
	 * installation manifest file and take appropriate action.
	 *
	 * @access	public
	 * @param	object	$element 	The xml node to process
	 * @return	mixed	Number of queries processed or False on error
	 * @since	1.5
	 */
	function parseQueries( $element, $fromdb, $todb, $toConfig, $site_id, $enteredvalues=array(), $template=null)
	{
		if (!is_a($element, 'JSimpleXMLElement') || !count($element->children())) {
			// Either the tag does not exist or has no children therefore we return zero files processed.
			return 0;
		}

		// Get the array of query nodes to process
		$queries = $element->children();
		if (count($queries) == 0) {
			// No queries to process
			return 0;
		}

      $fromPrefix       = $fromdb->getPrefix();
      $toPrefix         = $todb->getPrefix();
      $adminUserID      = (!empty( $template) && !empty( $template->adminUserID))    ? $template->adminUserID : '0';
      $adminUserLogin   = (!empty( $template) && !empty( $template->adminUserLogin)) ? $template->adminUserLogin : '';
		
		// Process each query in the $queries array (children of $tagName).
		$result = 0;
		foreach ($queries as $xmlquery)
		{
         $query = $xmlquery->data();
		   $query = ' ' . str_replace( array( '{toPrefix}', '{fromPrefix}', '#__', '{site_id}', '{adminUserID}', '{adminUserLogin}'), 
                                     array( $toPrefix, $fromPrefix, $toPrefix, $site_id, $adminUserID, $adminUserLogin), 
                                     $query
                                    );
			$todb->setQuery( $query);
			if (!$todb->query()) {}
			else                 { $result++; }
		}
		return $result;
	}
   
   //------------  createViews ---------------
   /**
    * @brief Create View to share the content of some tables
    *
    * The table prefix are important when the source and target database are identical and only differs in the prefix
    * @param sharedTables  Array with list of tables (without prefix) that must be shared
    *                      A sub 'views' array contain the resolved name of all the views
    *                      This list of views is used when creating the tables to "skip" a set of names
    */
   function createViews( $fromdb, $todb, &$sharedTables, $toConfig, $site_id, $enteredvalues=array(), $template=null)
   {
      $views  = array();
      $errors = array();
      $fromDBName = MultisitesDatabase::backquote($fromdb->_dbname);
      $fromPrefix     = $fromdb->getPrefix();
      $fromPrefix_len = strlen($fromPrefix);
      $toPrefix       = $todb->getPrefix();
      $toPrefix_len   = strlen($toPrefix);

      // Compute the real name of the views when LIKE pattern ' % ' is present in the table names
      // Compute all the name based on the "FROM" DB.
      foreach( $sharedTables['table'] as $tableName) {
         if ( $tableName == '[none]') {
            continue;
         }
         
         // remove the table prefix if it was present
         $tableName = str_replace('#__' , '', $tableName);
         
         // Resolve the potential table name in case where '%' is present
         $like = str_replace('_' , '\_', $fromPrefix.$tableName);
         $fromdb->setQuery( 'SHOW TABLES LIKE \''.$like.'\'' );
         $tables = $fromdb->loadResultArray();
         foreach($tables as $table)
         {
            // Keep the table name WITHOUT prefix
            $viewname = substr($table, $fromPrefix_len);
            // If the table is excluded then do not add it to the list
            if ( !empty( $sharedTables['tableexcluded']['#__'.$viewname])) {}
            // Otherwise, the table can be included in the list of views
            else {
               $views[$viewname] = $viewname;
            }
         }
      }
      
      // Replace the shared view with the resolved one
      $sharedTables['views'] = $views;
      
      foreach( $sharedTables['views'] as $tableName) {
         // Check if the VIEW already exists in the "TO" DB.
         $like = str_replace('_' , '\_', $toPrefix.$tableName);
         $query = "SHOW TABLES LIKE '$like'";
         $todb->setQuery($query);
         if ($todb->loadResult() == null) {
            $alreadyExists = false;
         }
         else {
            $alreadyExists = true;  // A table exists
         }
         
         // If the view does not exists, create it
         if ( !$alreadyExists) {
            $userName = MultisitesDatabase::_getDBUserName( $toConfig);
            
            if ( !empty( $sharedTables['tablewhere']['#__'.$tableName])) {
               $query = "CREATE OR REPLACE"
                      . " VIEW $toPrefix$tableName"
                      . " AS SELECT * FROM $fromDBName." . MultisitesDatabase::backquote( $fromPrefix.$tableName);

               if ( MultisitesDatabase::_isView( $fromdb, $fromPrefix.$tableName)) {
                  $from = MultisitesDatabase::getViewFrom( $fromdb, $fromPrefix.$tableName, true);
                  if ( !empty($from)) {
                     // If this is not a CREATE VIEW statement,
                     if ( strpos( $from, 'CREATE ALGORITHM=UNDEFINED') === false) {
                        // Use the view value
                        $query = "CREATE OR REPLACE"
                               . " VIEW $toPrefix$tableName"
                               . " AS SELECT * FROM ".$from;
                     }
                     // If this is not a CREATE VIEW statement
                     else {
                        // Try parse the statement with regular expression to extract the FROM DBName.DBTable
                        $fromUC = strtoupper( $from);
                        $p0 = strpos( $fromUC, 'SELECT');
                        if ( $p0 === false) {}
                        else {
                           $p1 = strpos( $fromUC, ' FROM ', $p0);
                           if ( $p1 === false) {}
                           else {
                              $p2 = strpos( $fromUC, ' ', $p1+5);
                              if ( $p2 === false) {}
                              else {
                                 $parts = explode( ' ', trim( substr( $from, $p2)));
                                 $fromArray = explode( '.', str_replace( array('`', '\''), array('', ''), $parts[0]));
      
                                 $physicalFromDBName = '';
                                 if ( count( $fromArray) > 1) {
                                    $physicalFromDBName = MultisitesDatabase::backquote( $fromArray[0])
                                                        . '.'
                                                        ;
                                    $physicalTableName = MultisitesDatabase::backquote( $fromArray[1]);
                                 }
                                 else {
                                    $physicalTableName = MultisitesDatabase::backquote( $fromArray[0]);
                                 }
      
                                 $query = "CREATE OR REPLACE"
                                        . " VIEW $toPrefix$tableName"
                                        . " AS SELECT * FROM ".$physicalFromDBName.$physicalTableName;
                              }
                           }
                        }
                     }
                  }
               }




               $query .= ' ' . str_replace( array( '{toPrefix}', '{fromPrefix}', '#__', '{site_id}'), 
                                            array( $toPrefix, $fromPrefix, $toPrefix, $site_id), 
                                            $sharedTables['tablewhere']['#__'.$tableName]
                                           );
            }
            else {
               $query = "CREATE OR REPLACE"
                      . " VIEW $toPrefix$tableName"
                      . " AS SELECT * FROM $fromDBName." . MultisitesDatabase::backquote( $fromPrefix.$tableName);
            }
            $todb->setQuery( $query );
            $result = $todb->query();
            if ( !$result) {
               $err_num = $todb->getErrorNum();
               // If access denied
               if ( $err_num == 1142 
                 || $err_num == 1449 
                  )
               {
                  $msg1 = "Error(1) [$err_num] query [$query]. DB Error: " . $todb->getErrorMsg();
                  // Try grant the user on the from table
         	      $table_name = MultisitesDatabase::backquote( $fromdb->_dbname)
         	                  . '.'
         	                  . MultisitesDatabase::backquote( $fromPrefix.$tableName)
         	                  ;
         	      $err = MultisitesDatabase::_createUser( $fromdb, $toConfig, $table_name);
         	      // Retry the creation of the view
                  $todb->setQuery( $query );
                  $result = $todb->query();
                  if ( !$result) {
                     $errors[] = $msg1;                        // Save the original error
                     $err_num = $todb->getErrorNum();
         			   if ( !empty( $err)) {
         			      $errors = array_merge($errors, $err);  // Also save the error on create user
         			   }
                     $msg = "Error [$err_num] retrying query [$query]. DB Error: " . $todb->getErrorMsg();
                     $errors[] = $msg;
                     continue;
                  }
               }
               else {
                  $msg = "Error [$err_num] query [$query]. DB Error: " . $todb->getErrorMsg();
                  $errors[] = $msg;
                  continue;
               }
            }
               
            // If there is additional queries to execute after creating the view
            if ( !empty( $sharedTables['tablequeries']['#__'.$tableName])) {
               foreach( $sharedTables['tablequeries']['#__'.$tableName] as $xmlqueries) {
                  $condition = $xmlqueries->attributes( 'if');
                  if ( !empty( $condition)) {
                     if ( isset( $test)) {
                        unset( $test);
                     }
                     // The eval condition must set the "$test" variable to true when it want the query is executed
                     eval( $condition);
                  }
                  
                  // When there is no condition or that the test is present and with true value
                  if ( empty( $condition) || !empty( $test)) {
                     MultisitesDatabase::parseQueries( $xmlqueries, 
                                                       $fromdb, $todb, $toConfig, $site_id, $enteredvalues, $template
                                                     );
                  }
               }
            }
         }
      } // Foreach tables
      return $errors;
   }
   
   //------------  copyDB ---------------
   /**
    * @brief Copy all the table present in a Source Database having a specific table prefix
    *        into a Target database using the target table prefix.
    *
    * The table prefix are important when the source and target database are identical and only differs in the prefix
    * @param Skip generally contain the list of shared table that were created in a previous step.
    *             This avoid rebuilding them when already created
    */
   function copyDB( $sourcedb, $targetdb, $toConfig, $skip=NULL, $structureOnly=false)
   {
      $errors = array();
      
// Debug2Win::debug( 'Before Source DB =' . var_export( $sourcedb, true) );
// Debug2Win::debug( 'Before Target DB =' . var_export( $targetdb, true) );
      
      $srcPrefix     = $sourcedb->getPrefix();
      $srcPrefix_len = strlen($srcPrefix);
      $dbprefix      = str_replace('_' , '\_', $srcPrefix);
      
      // Retreive all the table name present in the source database with corresponding prefix
      $tocopy = array();
      $sourcedb->setQuery( 'SHOW TABLES LIKE \''.$dbprefix.'%\'' );
      $tables = $sourcedb->loadResultArray();
      if ( empty( $tables)) {
         $errors[] = JText::_( 'SITE_DEPLOY_NO_TABLES');
      }
      
      if (( count( $tables) > 0)  && (!ini_get('safe_mode'))){ 
         // Try increase the time out limit in case where there are a very high number of tables
         // Consider a maximum of 5 second per table.
         set_time_limit( count( $tables) * 5);
      }

      foreach($tables as $table)
      {
         // Keep the table name WITHOUT prefix
         $tablename = substr($table, $srcPrefix_len);

         // If some tables must be excluded
         if (!empty($skip) && in_array($tablename, $skip))
            continue;
         
         // Save the table name to duplicate
         $tocopy[]=$tablename;
      }
      
      if ( empty( $tocopy)) {
         $errors[] = JText::_( 'SITE_DEPLOY_TO_COPY_ERR');
      }
// Debug2Win::debug( 'Replcating tables =' . var_export( $tocopy, true) );
      
      // Copy into the same DB
      $errors = MultisitesDatabase::copyDbTablesOrViews( $sourcedb, $targetdb, $toConfig,
                                                         $tocopy, $structureOnly);
      
// Debug2Win::debug( 'After Source DB =' . var_export( $sourcedb, true) );
// Debug2Win::debug( 'After Target DB =' . var_export( $targetdb, true) );

      return $errors;
   }




   //------------ _ComponentSubMenu ---------------
   /**
    * @brief Copy the sub-menu having the "fromParentID" to "toParentID"
    *
    * @par Implementation:
    * - Check if there is new "option" in the "#__components"
    */
   function _ComponentSubMenu( $fromdb, $todb, $fromParentID, $toParentID)
   {
      $errors = array();
      
      $db = $fromdb;
		$query = 'SELECT * FROM #__components WHERE parent=' .(int) $fromParentID;
		$db->setQuery($query);
      $rows = $db->loadObjectList();
      if ( !empty( $rows)) {
         foreach( $rows as $row) {
   			$query = 'REPLACE INTO #__components'
   			       . ' VALUES( 0, '.$todb->Quote($row->name).', '.$todb->Quote($row->link).', '.(int) $row->menuid.','
   				    . ' '.(int) $toParentID.', '.$todb->Quote($row->admin_menu_link).', '.$todb->Quote($row->admin_menu_alt).','
   				    . ' '.$todb->Quote($row->option).', '.(int) $row->ordering.', '.$todb->Quote($row->admin_menu_img).','
   				    . ' '.(int) $row->iscore.', '.$todb->Quote($row->params).', '.(int) $row->enabled.' )'
   				    ;
   			$todb->setQuery($query);
   			if (!$todb->query()) {
               $msg = "Install component sub-menu error query [$query]. DB Error: " . $todb->getErrorMsg();
               $errors[] = $msg;
   			}
   			else {
      			$menuid = $todb->insertid();
   			   $sub_errors = MultisitesDatabase::_ComponentSubMenu( $fromdb, $todb, $row->id, $menuid);
   			   $errors = array_merge($errors, $sub_errors);
   			}
         }
      }
      return $errors;
   }

   //------------ installNewComponents_j15 ---------------
   /**
    * @brief Compare 2 websites DB compnents and install in the "toDB" the new components
    *
    * @param component  When specified, it allow restrict the installation to the specified component.
    *                   Otherwise, this synchronise all the components
    * @par Implementation:
    * - Check if there is new "option" in the "#__components"
    */
   function installNewComponents_j15( $fromdb, $todb, $component = null, $overwrite = false)
   {
      $errors = array();
      
      // If Syncrhonize ALL components
      if ( empty( $component)) {
         // Retreive all the components installed in the "toDB"
         $query = "SELECT t.option FROM #__components AS t"
                . " WHERE t.parent = 0"
                ;
         $todb->setQuery( $query );
         $rows = $todb->loadResultArray();
         if ( !empty( $rows)) {
            $optionList = "'" . implode( "','", $rows) . "'";
            $where = " WHERE f.parent = 0 AND f.option NOT IN ($optionList)";
         }
         else {
            $where = " WHERE f.parent = 0";
         }
         
         // Search for all "parent" components present in the "fromDB" that are not present in the "toDB"
         $query = "SELECT * FROM #__components AS f"
                . $where
                ;
         $fromdb->setQuery( $query );
         $rows = $fromdb->loadObjectList();
         if ( empty( $rows)) {
            return $errors;
         }
      }
      // If try install a specific component
      else {
         // If overwrite
         if ( $overwrite) { }
         // If can not overwrite
         else {
            // Check that the component is not already present
            $query = "SELECT t.option FROM #__components AS t"
                   . " WHERE t.option = '$component' AND t.parent = 0"
                   ;
            $todb->setQuery( $query );
            $result = $todb->loadResult();
            // If component is present
            if ( !empty( $result)) {
               // Stop
               return $errors;
            }
         }
         
         // Read the "FROM" record
         $query = "SELECT * FROM #__components AS f"
                . " WHERE f.option = '$component' AND f.parent = 0"
                ;
         $fromdb->setQuery( $query );
         $rows = $fromdb->loadObjectList();
         if ( empty( $rows)) {
            return $errors;
         }
      }
      
      // For each new component
      $db = & $todb;
      foreach( $rows as $row) {
   		$exists = 0;
         // Insert or replace the parent
			$query = 'REPLACE INTO #__components'
			       . ' VALUES( '.$exists .', '.$db->Quote($row->name).', '.$db->Quote($row->link).', '.(int) $row->menuid.','
				    . ' '.(int) $row->parent.', '.$db->Quote($row->admin_menu_link).', '.$db->Quote($row->admin_menu_alt).','
				    . ' '.$db->Quote($row->option).', '.(int) $row->ordering.', '.$db->Quote($row->admin_menu_img).','
				    . ' '.(int) $row->iscore.', '.$db->Quote($row->params).', '.(int) $row->enabled.' )'
				    ;
			$db->setQuery($query);
			if (!$db->query()) {
            $msg = "Install component 'root' menu error query [$query]. DB Error: " . $todb->getErrorMsg();
            $errors[] = $msg;
            continue;
			}
			// Get the "parent" menuid (the current id)
			$menuid = $exists ? $exists : $db->insertid();
			
		   $sub_errors = MultisitesDatabase::_ComponentSubMenu( $fromdb, $todb, $row->id, $menuid);
		   $errors = array_merge($errors, $sub_errors);
      }
      return $errors;
   }

   //------------ _ComponentSubMenu_j16 ---------------
   /**
    * @brief Copy the menu and sub-menu having the "fromParentID" to "toParentID"
    *
    * @par Implementation:
    * - Check if there is new "option" in the "#__components"
    */
   function _ComponentSubMenu_j16( $fromdb, $todb, $fromComponentID, $fromParentID, $toComponentID, $toParentID)
   {
      $errors = array();
      
      $db = $fromdb;
		$query = 'SELECT * FROM #__menu WHERE component_id=' .(int)$fromComponentID . ' AND parent_id=' .(int) $fromParentID;
		$db->setQuery($query);
      $rows = $db->loadObjectList();
      if ( !empty( $rows)) {
         foreach( $rows as $row) {
   			$query = 'REPLACE INTO #__menu'
   			       . ' VALUES( 0, '.$todb->Quote($row->menutype)
   			       .', '.$todb->Quote( $row->title)
   			       .', '.$todb->Quote( $row->alias)
   			       .', '.$todb->Quote( $row->note)
   			       .', '.$todb->Quote( $row->path)
   			       .', '.$todb->Quote( $row->link)
   			       .', '.$todb->Quote( $row->type)
   			       .', '.(int) $row->published
   			       .', '.(int) $toParentID
   			       .', '.(int) $row->level
   			       .', '.(int) $toComponentID
   			       .', '.(int) $row->ordering
   			       .', '.(int) $row->checked_out
   			       .', '.$todb->Quote( $row->checked_out_time)
   			       .', '.(int) $row->browserNav
   			       .', '.(int) $row->access
   			       .', '.$todb->Quote( $row->img)
   			       .', '.(int) $row->template_style_id
   			       .', '.$todb->Quote( $row->params )
   			       .', '.(int) $row->lft
   			       .', '.(int) $row->rgt
   			       .', '.(int) $row->home
   			       .', '.$todb->Quote( $row->language)
   			       .', '.(int) $row->client_id
   				    .' )'
   				    ;
   			$todb->setQuery($query);
   			if (!$todb->query()) {
               $msg = "Install extension menu and sub-menu error query [$query]. DB Error: " . $todb->getErrorMsg();
               $errors[] = $msg;
   			}
   			else {
      			$menuid = $todb->insertid();
   			   $sub_errors = MultisitesDatabase::_ComponentSubMenu_j16( $fromdb, $todb, $fromComponentID, $row->id, $toComponentID, $menuid);
   			   $errors = array_merge($errors, $sub_errors);
   			}
         }
      }
      return $errors;
   }

   //------------ installNewComponents_j16 ---------------
   /**
    * @brief Compare 2 websites DB compnents and install in the "toDB" the new components
    *
    * @param component  When specified, it allow restrict the installation to the specified component.
    *                   Otherwise, this synchronise all the components
    * @par Implementation:
    * - Check if there is new "option" in the "#__extensions" with type="component"
    */
   public static function installNewComponents_j16( $fromdb, $todb, $component = null, $overwrite = false)
   {
      $errors = array();
      
      // If Syncrhonize ALL components
      if ( empty( $component)) {
         // Retreive all the components installed in the "toDB"
         $query = "SELECT DISTINCT t.element as 'option' FROM #__extensions AS t"
                . ' WHERE type="component"'
                ;
         $todb->setQuery( $query );
         $rows = $todb->loadResultArray();
         if ( !empty( $rows)) {
            $optionList = "'" . implode( "','", $rows) . "'";
            $where = " WHERE type=\"component\" AND f.element NOT IN ($optionList)";
         }
         else {
            $where = ' WHERE type="component"';
         }
         
         // Search for all "parent" components present in the "fromDB" that are not present in the "toDB"
         $query = "SELECT * FROM #__extensions AS f"
                . $where
                ;
         $fromdb->setQuery( $query );
         $rows = $fromdb->loadObjectList();
         if ( empty( $rows)) {
            return $errors;
         }
      }
      // If try install a specific component
      else {
         // If overwrite
         if ( $overwrite) { }
         // If can not overwrite
         else {
            // Check that the component is not already present
            $query = "SELECT t.element FROM #__extensions AS t"
                   . " WHERE t.type=\"component\" AND t.element = '$component'"
                   ;
            $todb->setQuery( $query );
            $result = $todb->loadResult();
            // If component is present
            if ( !empty( $result)) {
               // Stop
               return $errors;
            }
         }
         
         // Read the "FROM" record
         $query = "SELECT * FROM #__extensions AS f"
                . " WHERE f.type=\"component\" AND f.element = '$component'"
                ;
         $fromdb->setQuery( $query );
         $rows = $fromdb->loadObjectList();
         if ( empty( $rows)) {
            return $errors;
         }
      }
      
      // For each new component
      $db = & $todb;
      foreach( $rows as $row) {
   		$exists = 0;
         // Insert or replace the parent
			$query = 'REPLACE INTO #__extensions'
			       . ' VALUES( '.$exists 
			       .', '.$db->Quote($row->name)
			       .', '.$db->Quote($row->type)
			       .', '.$db->Quote($row->element)
			       .', '.$db->Quote($row->folder)
			       .', '.(int) $row->client_id
				    .', '.(int) $row->enabled
				    .', '.(int) $row->access
				    .', '.(int) $row->protected
			       .', '.$db->Quote($row->manifest_cache)
				    .', '.$db->Quote($row->params)
				    .', '.$db->Quote($row->custom_data)
				    .', '.$db->Quote($row->system_data)
				    .', '.(int) $row->checked_out
				    .', '.$db->Quote($row->checked_out_time)
				    .', '.(int) $row->ordering
				    .', '.(int) $row->state
				    .' )'
				    ;
			$db->setQuery($query);
			if (!$db->query()) {
            $msg = "Install extension 'root' menu error query [$query]. DB Error: " . $todb->getErrorMsg();
            $errors[] = $msg;
            continue;
			}
			// Get the "parent" menuid (the current id)
			$menuid = $exists ? $exists : $db->insertid();
			
		   $sub_errors = MultisitesDatabase::_ComponentSubMenu_j16( $fromdb, $todb, $row->extension_id, 1, $menuid, 1);
		   $errors = array_merge($errors, $sub_errors);
      }
      return $errors;
   }

   //------------ installNewComponents ---------------
   /**
    * @brief Compare 2 websites DB compnents and install in the "toDB" the new components
    *
    * @param component  When specified, it allow restrict the installation to the specified component.
    *                   Otherwise, this synchronise all the components
    * @par Implementation:
    * - Check if there is new "option" in the "#__components"
    */
   function installNewComponents( $fromdb, $todb, $component = null, $overwrite = false)
   {
      if ( version_compare( JVERSION, '1.6') >= 0) {
         return MultisitesDatabase::installNewComponents_j16( $fromdb, $todb, $component, $overwrite);
      }
      return MultisitesDatabase::installNewComponents_j15( $fromdb, $todb, $component, $overwrite);
   }


   //------------ _ModulesMenu ---------------
   /**
    * @brief Copy all records in the "#__modules_menu" tables having "fromModuleID" to "toModuleID"
    */
   function _ModulesMenu( $fromdb, $todb, $fromModuleID, $toModuleID)
   {
      $errors = array();

      // Searc for all "parent" components present in the "fromDB" that are not present in the "toDB"
      $query = "SELECT * FROM #__modules_menu"
             . " WHERE moduleid = " . (int)$fromModuleID
             ;
      $fromdb->setQuery( $query );
      $rows = $fromdb->loadObjectList();
      if ( empty( $rows)) {
         return $errors;
      }
      
      // For each modules
      foreach( $rows as $row) {
			if (!$todb->insertObject( '#__modules_menu', $row, 'id')) {
            $msg = "Install modules menu error query [$query]. DB Error: " . $todb->getErrorMsg();
            $errors[] = $msg;
            continue;
			}
      }
      return $errors;
   }

   //------------ installNewModules_j15 ---------------
   /**
    * @brief Compare 2 websites DB modules and install in the "toDB" new modules
    *
    * @par Implementation:
    * - Check if there is new "module" in the "#__modules"
    */
   function installNewModules_j15( $fromdb, $todb, $module = null, $overwrite = false)
   {
      $errors = array();
      
      // If Syncrhonize ALL modules
      if ( empty( $module)) {
         // Retreive all the modules installed in the "toDB"
         $query = "SELECT DISTINCT module FROM #__modules";
         $todb->setQuery( $query );
         $rows = $todb->loadResultArray();
         if ( !empty( $rows)) {
            $optionList = "'" . implode( "','", $rows) . "'";
            $where = " WHERE f.module NOT IN ($optionList)";
         }
         else {
            $where = '';
         }


         // Search for all modules present in the "fromDB" that are not present in the "toDB"
         $query = "SELECT * FROM #__modules AS f"
                . $where
                ;
         $fromdb->setQuery( $query );
         $rows = $fromdb->loadObjectList();
         if ( empty( $rows)) {
            return $errors;
         }
      }
      // If try install a specific module
      else {
         // If overwrite
         if ( $overwrite) { }
         // If can not overwrite
         else {
            // Check that the module is not already present
            $query = "SELECT module FROM #__modules AS t"
                   . " WHERE t.module = '$module'"
                   ;
            $todb->setQuery( $query );
            $result = $todb->loadResult();
            // If component is present
            if ( !empty( $result)) {
               // Stop
               return $errors;
            }
         }
         
         // Read the "FROM" record
         $query = "SELECT * FROM #__modules AS f"
                . " WHERE f.module = '$module'"
                ;
         $fromdb->setQuery( $query );
         $rows = $fromdb->loadObjectList();
         if ( empty( $rows)) {
            return $errors;
         }
      }
      
      
      // For each new modules
      $db = & $todb;
      foreach( $rows as $row) {
         $row->id                = null;
         $row->checked_out       = null;
         $row->checked_out_time  = null;
			if (!MultisitesDatabase::_replaceObject( $todb, '#__modules', $row, 'id')) {
            $msg = "Install modules DB Error: " . $todb->getErrorMsg();
            $errors[] = $msg;
            continue;
			}
			// Get the new moduleid (the current id)
			$moduleid = $row->id;
			
		   // Also install modules menu when present
		   $sub_errors = MultisitesDatabase::_ModulesMenu( $fromdb, $todb, $row->id, $moduleid);
		   $errors = array_merge($errors, $sub_errors);
      }
      return $errors;
   }


   //------------ installNewModules_j16 ---------------
   /**
    * @brief Compare 2 websites DB modules and install in the "toDB" new modules
    *
    * @par Implementation:
    * - Check if there is new "module" in the "#__modules"
    */
   public function installNewModules_j16( $fromdb, $todb, $module = null, $overwrite = false)
   {
      $errors = array();
      
      // If Syncrhonize ALL modules
      if ( empty( $module)) {
         // Retreive all the modules installed in the "toDB"
         $query = 'SELECT DISTINCT element as module FROM #__extensions WHERE type="module"';
         $todb->setQuery( $query );
         $rows = $todb->loadResultArray();
         if ( !empty( $rows)) {
            $optionList = "'" . implode( "','", $rows) . "'";
            $where = " WHERE type=\"module\" AND f.element NOT IN ($optionList)";
         }
         else {
            $where = '';
         }


         // Search for all modules present in the "fromDB" that are not present in the "toDB"
         $query = "SELECT * FROM #__extensions AS f"
                . $where
                ;
         $fromdb->setQuery( $query );
         $rows = $fromdb->loadObjectList();
         if ( empty( $rows)) {
            return $errors;
         }
      }
      // If try install a specific module
      else {
         // If overwrite
         if ( $overwrite) { }
         // If can not overwrite
         else {
            // Check that the module is not already present
            $query = "SELECT element as module FROM #__extensions AS t"
                   . " WHERE type=\"module\" AND t.element = '$module'"
                   ;
            $todb->setQuery( $query );
            $result = $todb->loadResult();
            // If component is present
            if ( !empty( $result)) {
               // Stop
               return $errors;
            }
         }
         
         // Read the "FROM" record
         $query = "SELECT * FROM #__extensions AS f"
                . " WHERE type=\"module\" AND f.element = '$module'"
                ;
         $fromdb->setQuery( $query );
         $rows = $fromdb->loadObjectList();
         if ( empty( $rows)) {
            return $errors;
         }
      }
      
      
      // For each new modules
      $db = & $todb;
      foreach( $rows as $row) {
         $row->extension_id      = null;
         $row->checked_out       = null;
         $row->checked_out_time  = null;
			if (!MultisitesDatabase::_replaceObject( $todb, '#__extensions', $row, 'extension_id')) {
            $msg = "Install j1.6 modules DB Error: " . $todb->getErrorMsg();
            $errors[] = $msg;
            continue;
			}
			// Get the new moduleid (the current id)
			$moduleid = $row->id;
			
		   // Also install modules menu when present
		   $sub_errors = MultisitesDatabase::_ModulesMenu( $fromdb, $todb, $row->id, $moduleid);
		   $errors = array_merge($errors, $sub_errors);
      }
      return $errors;
   }

   //------------ installNewModules ---------------
   /**
    * @brief Compare 2 websites DB modules and install in the "toDB" new modules
    *
    * @par Implementation:
    * - Check if there is new "module" in the "#__modules"
    */
   function installNewModules( $fromdb, $todb, $module = null, $overwrite = false)
   {
      if ( version_compare( JVERSION, '1.6') >= 0) {
         // Start to install the modules in the "extension" table
         $errors = MultisitesDatabase::installNewModules_j16( $fromdb, $todb, $module, $overwrite);
         if ( !empty( $errors)) {
            return $errors;
         }
         // When there is no error, continue to defined the modules definitions with current settings
      }
      return MultisitesDatabase::installNewModules_j15( $fromdb, $todb, $module, $overwrite);
   }

   //------------ installNewPlugins_j15 ---------------
   /**
    * @brief Compare 2 websites DB plugins and install in the "toDB" new plugins
    *
    * @par Implementation:
    * - Check if there is new "module" in the "#__plugins"
    */
   function installNewPlugins_j15( $fromdb, $todb, $folder = null, $element = null, $overwrite = false)
   {
      $errors = array();
      
      // If Syncrhonize ALL plugins
      if ( empty( $folder) && empty( $element)) {
         // Retreive all the plugins installed in the "toDB"
         $query = "SELECT CONCAT_WS( '/', folder, element) as opt FROM #__plugins AS t";
         $todb->setQuery( $query );
         $rows = $todb->loadResultArray();
         if ( !empty( $rows)) {
            $optionList = "'" . implode( "','", $rows) . "'";
            $where = " WHERE CONCAT_WS( '/', folder, element) NOT IN ($optionList)";
         }
         else {
            $where = '';
         }

         // Search for all the plugins present in the "fromDB" that are not present in the "toDB"
         $query = "SELECT * FROM #__plugins AS f"
                . $where
                ;

         $fromdb->setQuery( $query );
         $rows = $fromdb->loadObjectList();
         if ( empty( $rows)) {
            return $errors;
         }
      }
      // If try install a specific Plugin
      else {
         // If overwrite
         if ( $overwrite) { }
         // If can not overwrite
         else {
            // Check that the component is not already present
            $query = "SELECT folder, element FROM #__plugins AS t"
                   . " WHERE t.folder = '$folder' AND t.element = '$element'"
                   ;
            $todb->setQuery( $query );
            $result = $todb->loadResult();
            // If component is present
            if ( !empty( $result)) {
               // Stop
               return $errors;
            }
         }
         
         // Read the "FROM" record
         $query = "SELECT * FROM #__plugins AS f"
                . " WHERE f.folder = '$folder' AND f.element = '$element'"
                ;
         $fromdb->setQuery( $query );
         $rows = $fromdb->loadObjectList();
         if ( empty( $rows)) {
            return $errors;
         }
      }
      
      // For each new plugins
      $db = & $todb;
      foreach( $rows as $row) {
         // Insert the plugin
         $row->id                = null;
         $row->checked_out       = null;
         $row->checked_out_time  = null;
			if (!MultisitesDatabase::_replaceObject( $todb, '#__plugins', $row, 'id')) {
            $msg = "Install plugins DB Error: " . $todb->getErrorMsg();
            $errors[] = $msg;
            continue;
			}
      }
      return $errors;
   }


   //------------ installNewPlugins_j16 ---------------
   /**
    * @brief Compare 2 websites DB plugins and install in the "toDB" new plugins
    *
    * @par Implementation:
    * - Check if there is new "module" in the "#__plugins"
    */
   public static function installNewPlugins_j16( $fromdb, $todb, $folder = null, $element = null, $overwrite = false)
   {
      $errors = array();
      
      // If Syncrhonize ALL plugins
      if ( empty( $folder) && empty( $element)) {
         // Retreive all the plugins installed in the "toDB"
         $query = "SELECT CONCAT_WS( '/', folder, element) as opt FROM #__extensions AS t WHERE type=\"plugin\"";
         $todb->setQuery( $query );
         $rows = $todb->loadResultArray();
         if ( !empty( $rows)) {
            $optionList = "'" . implode( "','", $rows) . "'";
            $where = " WHERE type=\"plugin\" AND CONCAT_WS( '/', folder, element) NOT IN ($optionList)";
         }
         else {
            $where = ' WHERE type="plugin"';
         }

         // Search for all the plugins present in the "fromDB" that are not present in the "toDB"
         $query = "SELECT * FROM #__extensions AS f"
                . $where
                ;

         $fromdb->setQuery( $query );
         $rows = $fromdb->loadObjectList();
         if ( empty( $rows)) {
            return $errors;
         }
      }
      // If try install a specific Plugin
      else {
         // If overwrite
         if ( $overwrite) { }
         // If can not overwrite
         else {
            // Check that the plugin is not already present
            $query = "SELECT folder, element FROM #__extensions AS t"
                   . " WHERE type=\"plugin\" AND t.folder = '$folder' AND t.element = '$element'"
                   ;
            $todb->setQuery( $query );
            $result = $todb->loadResult();
            // If component is present
            if ( !empty( $result)) {
               // Stop
               return $errors;
            }
         }
         
         // Read the "FROM" record
         $query = "SELECT * FROM #__extensions AS f"
                . " WHERE f.type=\"plugin\" AND f.folder = '$folder' AND f.element = '$element'"
                ;
         $fromdb->setQuery( $query );
         $rows = $fromdb->loadObjectList();
         if ( empty( $rows)) {
            return $errors;
         }
      }
      
      // For each new plugins
      $db = & $todb;
      foreach( $rows as $row) {
         // Insert the plugin
         $row->extension_id      = null;
         $row->checked_out       = null;
         $row->checked_out_time  = null;
			if (!MultisitesDatabase::_replaceObject( $todb, '#__extensions', $row, 'extension_id')) {
            $msg = "Install_j1.6 plugins DB Error: " . $todb->getErrorMsg();
            $errors[] = $msg;
            continue;
			}
      }
      return $errors;
   }

   //------------ installNewPlugins ---------------
   /**
    * @brief Compare 2 websites DB plugins and install in the "toDB" new plugins
    *
    * @par Implementation:
    * - Check if there is new "module" in the "#__plugins"
    */
   function installNewPlugins( $fromdb, $todb, $folder = null, $element = null, $overwrite = false)
   {
      if ( version_compare( JVERSION, '1.6') >= 0) {
         return MultisitesDatabase::installNewPlugins_j16( $fromdb, $todb, $folder, $element, $overwrite);
      }
      return MultisitesDatabase::installNewPlugins_j15( $fromdb, $todb, $folder, $element, $overwrite);
   }

   //------------ verifyExtensions_j16 ---------------
   /**
    * @brief Verify the extensions definition against the double entries (case of Joomla 1.6 or higher)
    */
   function verifyExtensions_j16( $todb)
   {
      $errors = array();
      // Always use the master as reference
      $fromdb =& Jms2WinFactory::getMasterDBO();
      
      
      // --- Remove duplicated #__extensions
      // Remove the duplicated entries present in the extension (back-end only)
      $query = "SELECT t.element FROM #__extensions AS t GROUP BY t.type, t.element, t.client_id"
             . ' HAVING count( t.element)>=2 AND type="component" AND t.client_id=1'
             ;
             
      $todb->setQuery( $query );
      $rows = $todb->loadObjectList();
      if ( !empty( $rows)) {
         // For each component that have a multiple entries
         foreach( $rows as $row) {
            // Search for the one that correspond to the menu
            $query = 'SELECT t.extension_id FROM #__extensions AS t'
                   .' LEFT JOIN #__menu AS m ON m.component_id = t.extension_id'
                   .' WHERE m.id IS NULL AND t.type = "component" AND t.element='.$todb->Quote( $row->element)
                   ;
            $todb->setQuery( $query );
            $ext2DelRows = $todb->loadObjectList();
            if ( !empty( $ext2DelRows)) {
               foreach( $ext2DelRows as $ext2DelRow) {
                  $todb->setQuery( 'DELETE FROM #__extensions WHERE extension_id='.$ext2DelRow->extension_id);
                  $todb->query();
               }
            }
         }
      }
      
      // --- Update #__extensions->manifest_cache
      // Update all the manifest cache based on the master one
      // Do the operation manually in case where the MySQL user is not the same
      $query = 'SELECT t.extension_id, t.element, t.manifest_cache FROM #__extensions AS t WHERE type="component" AND t.client_id=1';
      $todb->setQuery( $query );
      $rows = $todb->loadObjectList();
      if ( !empty( $rows)) {
         // For each component,
         foreach( $rows as $row) {
            // Search for the one that correspond to the menu
            $query = 'SELECT f.extension_id, f.manifest_cache FROM #__extensions AS f WHERE type="component" AND f.client_id=1 AND f.element = '.$fromdb->quote( $row->element);
            $fromdb->setQuery( $query );
            $fromRows = $fromdb->loadObjectList();
            if ( !empty( $fromRows)) {
               foreach( $fromRows as $fromRow) {
                  if ( $row->manifest_cache != $fromRow->manifest_cache) {
                     $row->manifest_cache = $fromRow->manifest_cache;
                     $todb->updateObject( '#__extensions', $row, 'extension_id');
                     break;
                  }
               }
            }
         }
      }
      
      // --- Update the #__menus->alias / img
      $query = 'SELECT m.id, t.element, m.title, m.path, m.alias, m.img FROM #__extensions AS t, #__menu AS m WHERE m.component_id=t.extension_id AND t.type="component" AND t.client_id=1 AND t.extension_id>=10000';
      $todb->setQuery( $query );
      $rows = $todb->loadObjectList();
      if ( !empty( $rows)) {
         // For each menu,
         foreach( $rows as $row) {
            // retreive the menu->alias and menu->img present in the master
            $query = 'SELECT m.alias, m.img FROM #__extensions AS f, #__menu AS m'
                   .' WHERE m.component_id=f.extension_id AND f.type="component" AND f.client_id=1'
                   .' AND f.element='.$fromdb->quote( $row->element)
                   .' AND m.title='.$fromdb->quote( $row->title)
                   .' AND m.path='.$fromdb->quote( $row->path)
                   ;
            $fromdb->setQuery( $query );
            $fromRows = $fromdb->loadObjectList();
            if ( !empty( $fromRows)) {
               foreach( $fromRows as $fromRow) {
                  if ( $row->alias != $fromRow->alias
                    || $row->img   != $fromRow->img
                     )
                  {
                     $row->alias = $fromRow->alias;
                     $row->img   = $fromRow->img;
                     unset( $row->element);
                     $todb->updateObject( '#__menu', $row, 'id');
                  }
                  break;
               }
            }
         }
      }
      
      return $errors;
   }

   //------------ verifyExtensions ---------------
   /**
    * @brief Verify the #__extensions tables against the double entries
    */
   function verifyExtensions( $todb)
   {
      $errors = array();
      if ( version_compare( JVERSION, '1.6') >= 0) {
         return MultisitesDatabase::verifyExtensions_j16( $todb);
      }
      return $errors;
   }

   //------------ installNewExtension ---------------
   /**
    * @brief Compare 2 websites DB and in case where there is new extension present in the fromDB, 
    * replicate their definitions into the "toDB"
    *
    * @par Implementation:
    * - Check if there is new "option" in the "#__components"
    */
   function installNewExtension( $fromdb, $todb, $extension = null, $overwrite = false)
   {
      $errors = array();
      
      $component  = null;
      $module     = null;
      $folder     = null;
      $element    = null;
      if ( !empty( $extension)) {
         if ( strncmp( $extension, 'com_', 4) == 0) {
            $component = $extension;
         }
         else if ( strncmp( $extension, 'mod_', 4) == 0) {
            $module = $extension;
         }
         else {
            $parts = explode( '/', $extension);
            if (count( $parts) == 2) {
               $folder  = $parts[0];
               $element = $parts[1];
            }
         }
      }
      
      // If install ALL or only a specific component
      if ( empty( $extension) || !empty( $component)) {
         $errors = MultisitesDatabase::installNewComponents( $fromdb, $todb, $component, $overwrite);
      }

      // If install ALL or only a specific module
      if ( empty( $extension) || !empty( $module)) {
         $errorsModules = MultisitesDatabase::installNewModules( $fromdb, $todb, $module, $overwrite);
   	   $errors = array_merge($errors, $errorsModules);
   	}
      
      // If install ALL or only a specific plugin
      if ( empty( $extension) || !empty( $folder)) {
         $errorsPlugins = MultisitesDatabase::installNewPlugins( $fromdb, $todb, $folder, $element, $overwrite);
   	   $errors = array_merge($errors, $errorsPlugins);
   	}
   	
   	MultisitesDatabase::verifyExtensions( $todb);
      
      return $errors;
   }
   

   //------------ uninstallComponent_j15 ---------------
   /**
    * @brief Remove the menu entry corresponding to the component (option)
    */
   function uninstallComponent_j15( $db, $component)
   {
      $errors = array();
      
      // Retreive the components ID
      $query = "SELECT id FROM #__components as c"
             . ' WHERE c.parent = 0 AND c.option=' . $db->Quote( $component)
             ;
      $db->setQuery( $query );
      $id = $db->loadResult();
      if ( !empty( $id)) {
         // Delete all sub-menus
   		$query = 'DELETE FROM #__components WHERE parent = '.(int)$id;
         $db->setQuery($query);
         if ( !$db->query()) {
            $msg = "Error deleting component query [$query]. DB Error: " . $db->getErrorMsg();
            $errors[] = $msg;
         }

         // Delete the root menu
   		$query = 'DELETE FROM #__components WHERE id = '.(int)$id;
         $db->setQuery($query);
         if ( !$db->query()) {
            $msg = "Error 2 deleting component query [$query]. DB Error: " . $db->getErrorMsg();
            $errors[] = $msg;
         }
      }

      return $errors;
   }

   //------------ uninstallComponent_j16 ---------------
   /**
    * @brief Remove the menu entry corresponding to the component (option)
    */
   public static function uninstallComponent_j16( $db, $component)
   {
      $errors = array();
      
      // Retreive the components ID
      $query = "SELECT c.extension_id, m.id as menu_id FROM #__extensions as c"
             . ' LEFT JOIN #__menu as m ON component_id=extension_id'
             . ' WHERE c.type="component" AND c.element=' . $db->Quote( $component)
             ;
      $db->setQuery( $query );
      $row = $db->loadObject();
      if ( !empty( $row)) {
         $extension_id = $row->extension_id;
         $menu_id = $row->menu_id;
         
         // Delete all sub menus
         if ( !empty( $menu_id)) {
      		$query = 'DELETE FROM #__menu WHERE parent_id = '.(int)$menu_id;
            $db->setQuery($query);
            if ( !$db->query()) {
               $msg = "Error_1.6 deleting component submenu query [$query]. DB Error: " . $db->getErrorMsg();
               $errors[] = $msg;
            }
         }

         // Delete the root menu
         if ( !empty( $extension_id)) {
      		$query = 'DELETE FROM #__menu WHERE component_id = '.(int)$extension_id;
            $db->setQuery($query);
            if ( !$db->query()) {
               $msg = "Error_1.6 2 deleting root menu query [$query]. DB Error: " . $db->getErrorMsg();
               $errors[] = $msg;
            }
         }

         // Delete the component itself
         if ( !empty( $extension_id)) {
      		$query = 'DELETE FROM #__extensions WHERE extension_id = '.(int)$extension_id;
            $db->setQuery($query);
            if ( !$db->query()) {
               $msg = "Error_1.6 2 deleting component query [$query]. DB Error: " . $db->getErrorMsg();
               $errors[] = $msg;
            }
         }
      }

      return $errors;
   }

   //------------ uninstallComponent ---------------
   /**
    * @brief Remove the menu entry corresponding to the component (option)
    */
   function uninstallComponent( $db, $component)
   {
      if ( version_compare( JVERSION, '1.6') >= 0) {
         return MultisitesDatabase::uninstallComponent_j16( $db, $component);
      }
      return MultisitesDatabase::uninstallComponent_j15( $db, $component);
   }

   //------------ uninstallModule_j15 ---------------
   /**
    * @brief Uninstall a specific module (option).
    */
   function uninstallModule_j15( $db, $module)
   {
      $errors = array();
      
      // Retreive the list of modules ID that match the name
      $query = 'SELECT id FROM #__modules'
             . ' WHERE module=' .$db->Quote($module)
             ;
      $db->setQuery( $query );
		$modules = $db->loadResultArray();
		
		if (count($modules)) {
			JArrayHelper::toInteger($modules);
			$modID = implode(',', $modules);
			$query = 'DELETE' .
					' FROM #__modules_menu' .
					' WHERE moduleid IN ('.$modID.')';
			$db->setQuery($query);
         if ( !$db->query()) {
            $msg = "Error deleting modules_menu query [$query]. DB Error: " . $db->getErrorMsg();
            $errors[] = $msg;
         }
		}
		
		$query = 'DELETE FROM `#__modules` WHERE module = '.$db->Quote($module);
		$db->setQuery($query);
      if ( !$db->query()) {
         $msg = "Error deleting modules query [$query]. DB Error: " . $db->getErrorMsg();
         $errors[] = $msg;
      }

      return $errors;
   }

   //------------ uninstallModule_j16 ---------------
   /**
    * @brief Uninstall a specific module (option).
    */
   public static function uninstallModule_j16( $db, $module)
   {
      $errors = array();

      // -- First remove the module positions ---
      // Retreive the list of modules ID that match the name
      $query = 'SELECT id FROM #__modules'
             . ' WHERE module=' .$db->Quote($module)
             ;
      $db->setQuery( $query );
		$modules = $db->loadResultArray();

		$modules = $db->loadResultArray();
		
		if (count($modules)) {
			JArrayHelper::toInteger($modules);
			$modID = implode(',', $modules);
			$query = 'DELETE' .
					' FROM #__modules_menu' .
					' WHERE moduleid IN ('.$modID.')';
			$db->setQuery($query);
         if ( !$db->query()) {
            $msg = "Error deleting modules_menu query [$query]. DB Error: " . $db->getErrorMsg();
            $errors[] = $msg;
         }
		}
		
		$query = 'DELETE FROM `#__modules` WHERE module = '.$db->Quote($module);
		$db->setQuery($query);
      if ( !$db->query()) {
         $msg = "Error_1.6 deleting modules query [$query]. DB Error: " . $db->getErrorMsg();
         $errors[] = $msg;
      }

      // -- Now deleting the schema ---
		$query = 'DELETE FROM `#__schemas` WHERE extension_id IN ( SELECT extension_id FROM #__extensions WHERE element=' .$db->Quote($module) . ')';
		$db->setQuery($query);
      if ( !$db->query()) {
         $msg = "Error_1.6 deleting schema query [$query]. DB Error: " . $db->getErrorMsg();
         $errors[] = $msg;
      }


      // -- Finally, remove ALL the module definition ---
		$query = 'DELETE FROM `#__extensions` WHERE type="module" AND element=' .$db->Quote($module);
		$db->setQuery($query);
      if ( !$db->query()) {
         $msg = "Error_1.6 deleting extension module query [$query]. DB Error: " . $db->getErrorMsg();
         $errors[] = $msg;
      }
      

      return $errors;
   }

   //------------ uninstallModule ---------------
   /**
    * @brief Uninstall a specific module (option).
    */
   function uninstallModule( $db, $module)
   {
      if ( version_compare( JVERSION, '1.6') >= 0) {
         return MultisitesDatabase::uninstallModule_j16( $db, $module);
      }
      return MultisitesDatabase::uninstallModule_j15( $db, $module);
   }


   //------------ uninstallPlugin_j15 ---------------
   /**
    * @brief Unisintall a specific plugin defined by its folder and element
    */
   function uninstallPlugin_j15( $db, $folder, $element)
   {
      $errors = array();
		$query = 'DELETE FROM #__plugins'
				 . ' WHERE folder=' .  $db->Quote($folder)
				 . '   AND element=' . $db->Quote($element)
				 ;
		$db->setQuery($query);
		$db->query();

      return $errors;
   }

   //------------ uninstallPlugin_j16 ---------------
   /**
    * @brief Unisintall a specific plugin defined by its folder and element
    */
   public static function uninstallPlugin_j16( $db, $folder, $element)
   {
      $errors = array();

      // -- Now deleting the schema ---
		$query = 'DELETE FROM `#__schemas` WHERE extension_id IN ( SELECT extension_id FROM #__extensions WHERE folder=' .  $db->Quote($folder) 
		                                                                                                . ' AND element=' . $db->Quote($element) . ')';
		$db->setQuery($query);
      if ( !$db->query()) {
         $msg = "Error_1.6 deleting schema query [$query]. DB Error: " . $db->getErrorMsg();
         $errors[] = $msg;
      }

		$query = 'DELETE FROM #__extensions'
				 . ' WHERE folder=' .  $db->Quote($folder)
				 . '   AND element=' . $db->Quote($element)
				 ;
		$db->setQuery($query);
		$db->query();

      return $errors;
   }

   //------------ uninstallPlugin ---------------
   /**
    * @brief Unisintall a specific plugin defined by its folder and element
    */
   function uninstallPlugin( $db, $folder, $element)
   {
      if ( version_compare( JVERSION, '1.6') >= 0) {
         return  MultisitesDatabase::uninstallPlugin_j16( $db, $folder, $element);
      }
      return  MultisitesDatabase::uninstallPlugin_j15( $db, $folder, $element);
   }

   //------------ uninstallExtension ---------------
   /**
    * @brief Uninstall a specific extension based on its extension name (option)
    */
   function uninstallExtension( $db, $extension)
   {
      $errors = array();
      
      $component  = null;
      $module     = null;
      $plugin     = null;
      if ( !empty( $extension)) {
         if ( strncmp( $extension, 'com_', 4) == 0) {
            $component = $extension;
         }
         else if ( strncmp( $extension, 'mod_', 4) == 0) {
            $module = $extension;
         }
         else {
            $parts = explode( '/', $extension);
            if (count( $parts) == 2) {
               $folder  = $parts[0];
               $element = $parts[1];
            }
         }
      }
      
      // If uninstall component
      if ( !empty( $component)) {
         $errors = MultisitesDatabase::uninstallComponent( $db, $component);
      }

      // If uninstall module
      if ( !empty( $module)) {
         $errorsModules = MultisitesDatabase::uninstallModule( $db, $module);
   	   $errors = array_merge($errors, $errorsModules);
   	}
      
      // If uninstall plugin
      if ( !empty( $folder)) {
         $errorsPlugins = MultisitesDatabase::uninstallPlugin( $db, $folder, $element);
   	   $errors = array_merge($errors, $errorsPlugins);
   	}
      
      return $errors;
   }
   
   //------------  copyDBSharing ---------------
   /**
    * @brief Copy all the table present in a Source Database having a specific table prefix
    *        into a Target database using the target table prefix
    *        except the one that are described by DB sharing rules
    *
    * The table prefix are important when the source and target database are identical and only differs in the prefix
    */
   function copyDBSharing( $sourcedb, $targetdb, $sharedTables, $toConfig, $site_id, $enteredvalues, $template)
   {
      $errors = array();
      if ( empty( $sourcedb)) {
         $msg = 'Invalid "From" DB connection';
         $errors[] = $msg;
      }
      if ( empty( $targetdb)) {
         $msg = 'Invalid "To" DB connection';
         $errors[] = $msg;
      }
      if ( !empty( $errors)) {
         return $errors;
      }

      // First create the views corresponding to the "shared tables"
      $errors = MultisitesDatabase::createViews( $sourcedb, $targetdb, $sharedTables, $toConfig, $site_id, $enteredvalues, $template);
      if ( !empty( $errors)) {
         return $errors;
      }

      // Next copy the DB tables excluding the "shared tables"
      $errors = MultisitesDatabase::copyDB( $sourcedb, $targetdb, $toConfig, $sharedTables['views']);
      if ( !empty( $errors)) {
         return $errors;
      }
      
      // Syncrhonize the target DB in case where it is in fact an update of an existing target DB.
      // In case of an target DB update, also check if new extensions have been installed in the source DB
      $errors = MultisitesDatabase::installNewExtension( $sourcedb, $targetdb);
      
      return $errors;
   }
   
   //------------ _notifyEmail ---------------
   /**
    * @brief Send an email notification to the logged user
    */
   function _notifyEmail( $user, $enteredvalues, $template)
   {
		$me			= & JFactory::getUser();
		$adminEmail = $me->get('email');
		$adminName	= $me->get('name');
		if ( !empty( $enteredvalues['senderEmail'])) {
		   $adminEmail = $enteredvalues['senderEmail'];
   		$adminEmail = str_replace( array( '{user_email}', '{user_name}', '{site_name}', '{user_login}'),
            		                 array( $user->get('email'), $user->get('name'), $SiteName, $user->get('username')),  
            		                 $adminEmail);
   	   // Resolve additional keywords like {site_alias}
   	   $adminEmail = MultisitesDatabase::evalStr( $adminEmail, $site_id, '', null, $enteredvalues);
      }
		if ( !empty( $enteredvalues['senderName'])) {
		   $adminName = $enteredvalues['senderName'];
   		$adminName = str_replace( array( '{user_email}', '{user_name}', '{site_name}', '{user_login}'),
            		                 array( $user->get('email'), $user->get('name'), $SiteName, $user->get('username')),  
            		                 $adminName);
   	   // Resolve additional keywords like {site_alias}
   	   $adminName = MultisitesDatabase::evalStr( $adminName, $site_id, '', null, $enteredvalues);
      }


	   $site_id  = $enteredvalues['id'];
		$SiteName = $enteredvalues['toSiteName'];
		$SiteURL  = $enteredvalues['indexDomains'][0];
		

		// --- email subject ----
		if ( empty( $enteredvalues['emailSubject'])) { $subject = JText::_('SITE_DEPLOY_EMAIL_SUBJECT'); }
		else                                         { $subject = $enteredvalues['emailSubject']; }

		$subject = str_replace( array( '{user_name}', '{site_name}', '{site_url}', '{user_login}', '{user_psw}'),
		                        array( $user->get('name'), $SiteName, $SiteURL, $user->get('username'), $user->password_clear),  
		                        $subject);

	   // Resolve additional keywords like {site_alias}
	   $subject = MultisitesDatabase::evalStr( $subject, $site_id, '', null, $enteredvalues);

		// --- email body ----
		if ( empty( $enteredvalues['emailBody']))    { $body = JText::_('SITE_DEPLOY_EMAIL_BODY'); }
		else                                         { $body = $enteredvalues['emailBody']; }
		
		$message = str_replace( array( '{user_name}', '{site_name}', '{site_url}', '{user_login}', '{user_psw}'),
		                        array( $user->get('name'), $SiteName, $SiteURL, $user->get('username'), $user->password_clear),  
		                        $body);

	   // Resolve additional keywords like {site_alias}
	   $message = MultisitesDatabase::evalStr( $message, $site_id, '', null, $enteredvalues);

		
		// --- to email address ----
		$to_email_address = $user->get('email');
		if ( !empty( $enteredvalues['emailToAddress'])) {
		   $to_email_address = $enteredvalues['emailToAddress'];
   		$to_email_address = str_replace( array( '{user_name}', '{site_name}', '{user_login}'),
            		                        array( $user->get('name'), $SiteName, $user->get('username')),  
            		                        $to_email_address);
   	   // Resolve additional keywords like {site_alias}
   	   $to_email_address = MultisitesDatabase::evalStr( $to_email_address, $site_id, '', null, $enteredvalues);
      }
		
		JUtility::sendMail( $adminEmail, $adminName, $to_email_address, $subject, $message );
   }

   //------------ _updateAdminUser ---------------
   /**
    * @brief Set a new email and password for the administrator user.
    * @param $toDB            The database where the admin user must be modified
    * @param $enteredvalues   An array with new admin user values;
    *                         - newAdminPsw   = New administrator password;
    *                         - newAdminEmail = New Admonistrator email.
    * @param $template        The template where can be found the user id that must be modified.
    *                         If the template is not specified or the admin user id does not exists,
    *                         then the routine use the first Super Administrator ID present in the 'to' database
    *                         to update the email and password.
    */
   function _updateAdminUser( $toDB, $enteredvalues, $template)
   {
      require_once(  JPATH_ADMINISTRATOR.DIRECTORY_SEPARATOR.'components'.DIRECTORY_SEPARATOR.'com_multisites' .DIRECTORY_SEPARATOR. 'libraries' .DIRECTORY_SEPARATOR. 'joomla' .DIRECTORY_SEPARATOR. 'multisitesfactory.php');
      
      // If there is no administrator email or password and not forced to send notification
      if ( empty( $enteredvalues['newAdminPsw']) && empty( $enteredvalues['newAdminEmail'])
         && (empty( $template) || (empty( $template->adminUserLogin) && empty( $template->adminUserName) && empty( $template->adminUserEmail) && empty( $template->adminUserPsw)) )
         && empty( $enteredvalues['notifyUser'])
         )
      {
         // Do nothing
         return;
      }
      $notifyUser = false;
      if ( !empty( $enteredvalues['notifyUser'])) {
         $notifyUser = $enteredvalues['notifyUser'];
      }

		// Replace the Joomla DB instance with the "toDB"
		$sav_db  =& MultisitesFactory::setDBO( $toDB);
		$db      =& JFactory::getDBO();

      
      // If there is an administrator User ID defined in the template
      if ( !empty( $template) && !empty( $template->adminUserID)) {
         // Use the template admin user id
   		$user_id = $template->adminUserID;
	   }
	   else {
	      // Try to find the first Super Administrator
         $query = 'SELECT id, username FROM #__users'
                . ' WHERE gid=25 AND block=0'
                . ' ORDER BY id';
   		$db->setQuery( $query );
   		$row = $db->loadObject();
   		$user_id = $row->id;
      }
		
		$user = new JUser($user_id);
      if ( !empty( $enteredvalues['newAdminPsw'])) {
         $newAdminPsw = $enteredvalues['newAdminPsw'];
   		$params['password']  = $newAdminPsw;
   		$params['password2'] = $newAdminPsw;
      }
      else if ( !empty( $template) && !empty( $template->adminUserPsw)) {
         if ( $template->adminUserPsw == '{user_psw}') {
            $curuser =& JFactory::getUser();
            $user->set( 'password', $curuser->password);
         }
         else {
            $newAdminPsw = MultisitesDatabase::evalStr( $template->adminUserPsw, '', '', '', array());
      		$params['password']  = $newAdminPsw;
      		$params['password2'] = $newAdminPsw;
            // Notify new password
            $notifyUser = true;
         }
      }
      if ( !empty( $enteredvalues['newAdminEmail'])) {
         $params['email'] = $enteredvalues['newAdminEmail'];
      }
      else if ( !empty( $template)) {
         $curuser =& JFactory::getUser();
         if ( !empty( $template->adminUserLogin)) {
      	   $params['username']  = str_replace( '{user_login}', 
            	                                   $curuser->username, 
            	                                   $template->adminUserLogin
            	                                 );
      	}
         if ( !empty( $template->adminUserName)) {
      	   $params['name']  = str_replace( '{user_name}', 
      	                                   $curuser->name, 
      	                                   $template->adminUserName
      	                                 );
      	}
         if ( !empty( $template->adminUserEmail)) {
      	   $params['email'] = str_replace( '{user_email}', 
      	                                   $curuser->email, 
      	                                   $template->adminUserEmail
      	                                 );
      	}
      }


		// If there is something to update for the "new admin user"
      if ( !empty( $params)) {
         $user->bind( $params);
         if ( version_compare( JVERSION, '1.6') >= 0) {
      		$user->save( true);
         }
         else {
      		// Force current as Super Admin to allow the update
      		$my =& JFactory::getUser();
      		$sav_gid = $my->gid;
      		$my->gid = 25;   
      		$user->save( true);
      		$my->gid = $sav_gid;   // Restore the normal rights
         }
   		
		}
		
		// Restore the original Joomla DB instance
		MultisitesFactory::setDBO( $sav_db);
		
		if ( $notifyUser) {
		   MultisitesDatabase::_notifyEmail( $user, $enteredvalues, $template);
		}
		
   }

   //------------ _config_MediaSettings ---------------
   /**
    * @brief Configure the "to" media settings.
    *
    * When the user has defined a specific media directory, this routine is used to setup the "to" database
    * with the new media folder information and also to replicate the media files and folder that are present
    * in the from website.
    *
    * @par Implementation:
    * - Read the "FROM" media settings (in particular the folder path).
    * - Read the "TO" media settings
    * - Compute the new "TO" media folder path using the "site" specific or "template" media folder definition.
    * - replicate the "FROM" media folder into the "TO" media folder (copy files and folders)
    * - Compute the new "TO" image folder path using the "site" specific or "template" media folder definition.
    * - replicate the "FROM" image folder into the "TO" image folder (copy files and folders)
    * - Update the "TO" DB with new compute media folder and image folder
    */
   function _config_MediaSettings( $fromSiteID, $fromDB, $toDB, $enteredvalues, $site_id, $site_dir, $deploy_dir, $dbInfo, $template)
   {
      jimport( 'joomla.registry.registry');
      jimport( 'joomla.filesystem.path');
      require_once( JPATH_COMPONENT_ADMINISTRATOR .DS. 'libraries' .DS. 'joomla' .DS. 'jms2winfactory.php');

// Debug2Win::enableStandalone();      // Write the log in administrator/components/com_multisites/classes/logs
// Debug2Win::setFileName( 'multisites.media.log.php');
// Debug2Win::enableDebug();        // Remove the comment to enable the debugging

// Debug2Win::debug_start( '>> _config_MediaSettings() - START');

      $doUpdate = false;

      // Connect and read the com_media parameters of the FROM DB
// Debug2Win::debug( 'From DB =' . var_export( $fromDB, true) );
      if ( version_compare( JVERSION, '1.6') >= 0) {
			$table =& JTable::getInstance('extension', 'JTable', array( 'dbo' => $fromDB));
// Debug2Win::debug( 'Table =' . var_export( $table, true) );
			$table->load( array( 'type' => 'component', 'element' => 'com_media') );
      } else {
			$table =& JTable::getInstance('component', 'JTable', array( 'dbo' => $fromDB));
			$table->loadByOption( 'com_media' );
// Debug2Win::debug( 'loadByOption Table =' . var_export( $table, true) );
		}
		$fromMediaParams   = array();
		$registry = new JRegistry();
      if ( version_compare( JVERSION, '1.6') >= 0) {
			$registry->loadJSON( $table->params);
		}
		else {
   		$registry->loadINI( $table->params);
		}
		$fromMediaParams['params'] = $registry->toArray();
		// If the media path can not be found
		if ( empty( $fromMediaParams['params']['file_path'])) {
// Debug2Win::debug( 'From Media params =' . var_export( $fromMediaParams['params'], true) );
			JError::raiseWarning( 500, JText::_( 'SITE_DEPLOY_FROM_MEDIA_ERR'));
			return false;
		}

		// Connect on TO DB and read the current com_media parameters
      if ( version_compare( JVERSION, '1.6') >= 0) {
			$table =& JTable::getInstance('extension', 'JTable', array( 'dbo' => $toDB));
			$table->load( array( 'type' => 'component', 'element' => 'com_media') );
      } else {
			$table =& JTable::getInstance('component', 'JTable', array( 'dbo' => $toDB));
			$table->loadByOption( 'com_media' );
		}
		$toMediaParams   = array();
		$registry = new JRegistry();
      if ( version_compare( JVERSION, '1.6') >= 0) {
			$registry->loadJSON( $table->params);
		}
		else {
   		$registry->loadINI( $table->params);
		}
		$toMediaParams['params'] = $registry->toArray();

// Debug2Win::debug( "Computing Media folder ...");
      // Compute the new Media Folder value
      if ( !empty( $enteredvalues['media_dir'])) {
         $file_path = JPath::clean( MultisitesDatabase::evalStr( $enteredvalues['media_dir'], $site_id, $site_dir, $deploy_dir, $dbInfo));
      }
      else if ( !empty( $template) && !empty( $template->media_dir)) {
         $file_path = JPath::clean( MultisitesDatabase::evalStr( $template->media_dir, $site_id, $site_dir, $deploy_dir, $dbInfo));
      }
      // If wants to replicate the media folder
      if ( !empty( $file_path)) {
   		//Sanitize $file_path
   		if(strpos($file_path, '/') === 0 || strpos($file_path, '\\') === 0) {
   			//Leading slash.  Kill it and default to /media
   			$file_path = 'images';
   		}
   		if(strpos($file_path, '..') !== false) {
   			//downward directories.  Kill it and default to images/
   			$file_path = 'images';
   		}

         // Compute the FROM directory (for the replication)
         if ( empty( $fromSiteID) || $fromSiteID==':master_db:') {
            $from_dir = JPATH_ROOT .DS. $fromMediaParams['params']['file_path'];
         }
         // Slave site
         else {
            $from_dir = Jms2WinFactory::getSlaveRootPath( $fromSiteID) .DS. $fromMediaParams['params']['file_path'];
            // If the slave directory does not exist,
            if ( !JFolder::exists( $from_dir)) {
               // Use the master directory as rescue one
               $from_dir = JPATH_ROOT .DS. $fromMediaParams['params']['file_path'];
            }
         }
   		
   		// Save the new value that will be stored into the DB
   		$toMediaParams['params']['file_path'] = str_replace( '\\', '/', $file_path);
   		
   		// Normally the path is relative. So convert it into an absolute path
   		if ( empty( $deploy_dir)) {
   		   $to_dir = JPATH_ROOT . '/' . $toMediaParams['params']['file_path'];
   		}
   		else {
   		   $to_dir = $deploy_dir . '/' . $toMediaParams['params']['file_path'];
   		}
   		
// Debug2Win::debug( "Replicate Media: fromDir [$from_dir] -> toDir [$to_dir]");
   		// If different directories and the target directory is not present,
   		if ( $from_dir != $to_dir && !JFolder::exists( $to_dir)) {
   		   // Check if the "from_dir" exists
   		   if ( !JFolder::exists( $from_dir)) {
      			JError::raiseWarning( 500, "Unable to replicate the media folder due to missing source [$from_dir]" );
      			return false;
   		   }
   		   // Try to copy the directory
   		   else if ( !JFolder::copy( $from_dir, $to_dir)) {
      			JError::raiseWarning( 500, $table->getError() );
      			return false;
      		}
   		}
   		
   		$doUpdate = true;
      }
      
// Debug2Win::debug( "Computing Image folder ...");
      // Compute the new Images Folder value
      if ( !empty( $enteredvalues['images_dir'])) {
         $image_path = JPath::clean( MultisitesDatabase::evalStr( $enteredvalues['images_dir'], $site_id, $site_dir, $deploy_dir, $dbInfo));
      }
      else if ( !empty( $template) && !empty( $template->images_dir)) {
         $image_path = JPath::clean( MultisitesDatabase::evalStr( $template->images_dir, $site_id, $site_dir, $deploy_dir, $dbInfo));
      }
      
      if ( isset( $image_path)) {
         if ( version_compare( JVERSION, '1.6') >= 0) {
            $image_path_constant = 'images';
         }
         else {
            $image_path_constant = 'images/stories';
         }

   		//Sanitize $file_path and $image_path
   		if(strpos($image_path, '/') === 0 || strpos($image_path, '\\') === 0) {
   			//Leading slash.  Kill it and default to /media
   			$image_path = $image_path_constant;
   		}
   		if(strpos($image_path, '..') !== false) {
   			//downward directories  Kill it and default to images/stories
   			$image_path = $image_path_constant;
   		}

         // Compute the FROM directory (for the replication)
         if ( empty( $fromSiteID) || $fromSiteID==':master_db:') {
            $from_dir = JPATH_ROOT .DS. $fromMediaParams['params']['image_path'];
         }
         // Slave site
         else {
            $from_dir = Jms2WinFactory::getSlaveRootPath( $fromSiteID) .DS. $fromMediaParams['params']['image_path'];
            // If the slave directory does not exist,
            if ( !JFolder::exists( $from_dir)) {
               // Use the master directory as rescue one
               $from_dir = JPATH_ROOT .DS. $fromMediaParams['params']['image_path'];
            }
         }


   		$toMediaParams['params']['image_path'] = str_replace( '\\', '/', $image_path);

   		// Normally the path is relative. So convert it into an absolute path
   		if ( empty( $deploy_dir)) {
   		   $to_dir = JPATH_ROOT . '/' . $toMediaParams['params']['image_path'];
   		}
   		else {
   		   $to_dir = $deploy_dir . '/' . $toMediaParams['params']['image_path'];
   		}

// Debug2Win::debug( "Replicate Image: fromDir [$from_dir] -> toDir [$to_dir]");
   		// If different directories and the target directory is not present,
   		if ( $from_dir != $to_dir && !JFolder::exists( $to_dir)) {
   		   // Check if the "from_dir" exists
   		   if ( !JFolder::exists( $from_dir)) {
      			JError::raiseWarning( 500, "Unable to replicate the image folder due to missing source [$from_dir]" );
      			return false;
   		   }
   		   // Try to copy the directory
   		   else if ( !JFolder::copy( $from_dir, $to_dir)) {
      			JError::raiseWarning( 500, $table->getError() );
      			return false;
      		}
   		}

   		$doUpdate = true;
   	}
		
		// If there is something to write
		if ( $doUpdate) {
   		// Set the new values
   		$table->bind( $toMediaParams );
   
   		// pre-save checks
   		if (!$table->check()) {
   			JError::raiseWarning( 500, $table->getError() );
   			return false;
   		}
   
   		// save the changes
   		if (!$table->store()) {
   			JError::raiseWarning( 500, $table->getError() );
   			return false;
   		}
		}
		
// Debug2Win::debug_stop( '<< _config_MediaSettings() - STOP');
   }

   //------------ configureDB ---------------
   /**
    * @brief Update the database to change some configuration parameters (admin password, media folder, image folder, ...).
    */
   function configureDB( $fromSiteID, $fromDB, $toDB, $enteredvalues, $site_id, $site_dir, $deploy_dir, $dbInfo, $template)
   {
      $errors = array();
      
      // Update the Administrator Password if present
      MultisitesDatabase::_updateAdminUser( $toDB, $enteredvalues, $template);

      // Update the Media and Images folder settings
      MultisitesDatabase::_config_MediaSettings( $fromSiteID, $fromDB, $toDB, $enteredvalues, $site_id, $site_dir, $deploy_dir, $dbInfo, $template);
      
      return $errors;
   }



// ===========================================================
//             Code based on the Joomla installation
// ===========================================================


   //------------ getDBO ---------------
	function & getDBO($driver, $host, $user, $password, $database, $prefix, $select = true, $forceRetry=false)
	{
		static $db;

		if ( ! $db || $forceRetry)
		{
			jimport('joomla.database.database');
			$options	= array ( 'driver' => $driver, 'host' => $host, 'user' => $user, 'password' => $password, 'database' => $database, 'prefix' => $prefix, 'select' => $select	);
			$db = & JDatabase::getInstance( $options );
// Debug2Win::debug( "result getDBO : " . var_export( $options, true));
		}

		return $db;
	}

   //------------ createDatabase ---------------
	/**
	 * Creates a new database
	 * @param object Database connector
	 * @param string Database name
	 * @param boolean utf-8 support
	 * @return boolean success
	 */
	function createDatabase(& $db, $DBname, $DButfSupport=true)
	{
      $errors = array();
      
		if ($DButfSupport) {
			$sql = "CREATE DATABASE `$DBname` CHARACTER SET `utf8`";
		}
		else {
			$sql = "CREATE DATABASE `$DBname`";
		}

		$db->setQuery($sql);
		$db->query();
		$result = $db->getErrorNum();

		if ($result != 0)
		{
         $msg = JText::sprintf('SITE_DEPLOY_CREATEDB_ERR', $result, $sql, $db->getErrorMsg());
         $errors[] = $msg;
		}

		// Commit the DB creation to ensure that all the DB connection will be informed of the DB
		$db->setQuery('COMMIT');
		$db->query();


		return $errors;
	}


   //------------ setDBCharset ---------------
	/**
	 * Sets character set of the database to utf-8 with selected collation
	 * Used in instances of pre-existing database
	 * @param object Database object
	 * @param string Database name
	 * @param string Selected collation
	 * @return boolean success
	 */
	function setDBCharset(& $db, $DBname)
	{
		if ($db->hasUTF())
		{
			$sql = "ALTER DATABASE `$DBname` CHARACTER SET `utf8`";
			$db->setQuery($sql);
			$db->query();
			$result = $db->getErrorNum();
			if ($result != 0) {
				return false;
			}
		}
		return true;
	}
	
   //------------ _getServerIP ---------------
   /**
    *  @brief Return the IP address of this server
    *
    */
	function _getServerIP( $toConfig)
	{
	   $ip = null;
	   // If there is an hardcoded value present in the "multisites.cfg.php" file
	   if ( defined( 'MULTISITES_DB_GRANT_HOST')) {
	      $ip = MULTISITES_DB_GRANT_HOST;
   	   if ( !empty( $ip)) {
   	      return $ip;
   	   }
	   }
	   // Try retreive the IP address of the server
	   // On Windows, use LOCAL_ADDR
 	   if ( !empty( $_SERVER['LOCAL_ADDR'])) {
	      $ip = $_SERVER['LOCAL_ADDR'];
	   }
	   // On Unix, use SERVER_ADDR
	   else if ( !empty( $_SERVER['SERVER_ADDR'])) {
         $ip = $_SERVER['SERVER_ADDR'];
	   }
	   // If this is a localhost
	   if ( $ip == '127.0.0.1') {
	      // And the DB server is also present on the localhost
   	   $toHost = $toConfig->getValue( 'config.host');
	      if ( $toHost == '127.0.0.1' || $toHost == 'localhost') {
   	      return $ip;
   	   }
   	   // If DB server is located on another machine
   	   else {
   	      // We can not use the localhost IP address and we absolutely need to use a real address
   	      // As we have not found it, accept all server (wildcard)
      	   return null;
   	   }
	   }
	   return $ip;
	}

   //------------ _getDBUserName ---------------
	/**
	 * @brief Get DB user name corresponding to the "to configuration".
	 */
	function _getDBUserName( $toConfig)
	{
	   $ip = MultisitesDatabase::_getServerIP( $toConfig);
	   if ( empty( $ip)) {
	      $user_name  = MultisitesDatabase::backquote( $toConfig->getValue( 'config.user'))
	                  . '@'
	                  . MultisitesDatabase::backquote( '%')
	                  ;
	   }
	   else {
	      $user_name  = MultisitesDatabase::backquote( $toConfig->getValue( 'config.user'))
	                  . '@'
	                  . MultisitesDatabase::backquote( $ip)
	                  ;
	   }
	   
	   return $user_name;
	}


   //------------ _createUser ---------------
	/**
	 * @brief Create the new user and password corresponding to the new DB
	 */
	function _createUser( & $db, $toConfig, $table_name = null)
	{
      $errors = array();
      
	   if ( empty( $table_name)) {
	      $table_name = MultisitesDatabase::backquote( $toConfig->getValue( 'config.db'))
	                  . '.*';
	   }
	   
      $user_name  = MultisitesDatabase::_getDBUserName( $toConfig);
	   $toDBPsw    = $toConfig->getValue( 'config.password');
	   
		$query = "GRANT ALL PRIVILEGES ON $table_name"
		       . " TO $user_name"
		       . (empty($toDBPsw) ? '' : " IDENTIFIED BY '$toDBPsw'")
		       . " WITH GRANT OPTION;"
		       ;
		$db->setQuery( $query);
		$db->query();
		$result = $db->getErrorNum();
		if ($result != 0) {
         $msg = JText::sprintf('SITE_DEPLOY_CREATEUSER_ERR', $result, $query, $db->getErrorMsg());
         $errors[] = $msg;
if ( defined( 'MULTISITESDB_DEBUG')) {
   ob_start();
   debug_print_backtrace();
   $stack = ob_get_contents();
   ob_end_clean();
   Debug2Win::debug( "_createUser ERROR query=[$query] db: " . var_export( $db, true) . $stack);
}
			return $errors;
		}
		
		$db->setQuery( "FLUSH PRIVILEGES;");
		$db->query();

		return $errors;
	}


   //------------ makeDB ---------------
	function makeDB($fromConfig, $toConfig)
	{
if ( defined( 'MULTISITESDB_DEBUG')) { Debug2Win::debug_start( '>> makeDB() - START'); }

      $errors = array();
      
	   $DBtype     = $fromConfig->getValue( 'config.dbtype');
	   $DBhostname = $toConfig->getValue( 'config.host');
	   $DBname     = $toConfig->getValue( 'config.db');
	   
	   $DBuserName = $fromConfig->getValue( 'config.user');
	   $DBpassword = $fromConfig->getValue( 'config.password');
	   
	   if ( defined( 'MULTISITES_DB_ROOT_USER')) {
	      $DBuserName = MULTISITES_DB_ROOT_USER;
   	   if ( defined( 'MULTISITES_DB_ROOT_PSW')) {
   	      $DBpassword  = MULTISITES_DB_ROOT_PSW;
   	   }
	   }

	   $DBPrefix   = $toConfig->getValue( 'config.dbprefix');

	   $toDBUser   = $toConfig->getValue( 'config.user');
	   $toDBPsw    = $toConfig->getValue( 'config.password');


		$toOptions = array( 'driver'     => $DBtype,
		                    'host'       => $DBhostname,
		                    'user'       => $toDBUser,
		                    'password'   => $toDBPsw,
		                    'database'   => $DBname,
		                    'prefix'     => $DBPrefix
		                    );

		$rootOptions               = $toOptions;
		$rootOptions['user']       = $DBuserName;
		$rootOptions['password']   = $DBpassword;

		// If the user can login on MySQL
		$dbTo = & MultisitesDatabase::getInstance( $toOptions, 'none');
		if ( $dbTo->isConnected()) {
		   // This is OK
		}
		// Otherwise
		else {
		   // Try creating a new user using the Root Login info
   		$dbRoot = & MultisitesDatabase::getInstance( $rootOptions, 'user');
   		if ( $dbRoot->isConnected()) {
   		   // If creation of the user is successfull
   		   if ( $dbRoot->createUser( $toConfig)) {
   		      // Retry login MySQL with the 'to User'
         		$dbTo = & MultisitesDatabase::getInstance( $toOptions, 'none');
         		if ( $dbTo->isConnected()) {
         		   // This is OK
         		}
         		else {
                  $msg = "MakeDB - Unable to login with the new user [$toDBUser].";
                  $errors[] = $msg;
         			return $errors;
         		}
   		   }
   		   else {
      			// Creation of the "toUser" failed
               $msg = "MakeDB - Unable to create the user [$toDBUser] dynamically.";
               $errors[] = $msg;
      			return $errors;
   		   }
   		}
   		else {
   		   // Unable to login as root
   			// connection failed
            $msg = 'MakeDB - Unable to login as Root to create a DB user dynamically.';
            $errors[] = $msg;
   			return $errors;
   		}
		}
		
		// If the "toUser" can connect on the "toDB"
		if ( $dbTo->select_db()) {
		   // Do nothing
		}
		// When the "to User" can not connect on the "to DB"
		else {
		   // This can be either due to missing DB or that the user has not the privilege to connect on the DB

		   // Try creating the DB using the Root options
   		$dbRoot = & MultisitesDatabase::getInstance( $rootOptions, 'user');
   		if ( $dbRoot->isConnected()) {
   		   // If the root user can select the DB
      		if ( $dbRoot->select_db( $toOptions['database'])) {
      		   // This means that the "to User" has not the privileges

      		   // Try creating of the "to user"
      		   if ( $dbRoot->createUser( $toConfig)) {
      		      // Reconnect the 'toDB'
            		$dbTo = & MultisitesDatabase::getInstance( $toOptions, 'none');
            		if ( $dbTo->isConnected()) {
            		   // This is OK
            		}
            		else {
                     $msg = "MakeDB - Unable creating the new user [$toDBUser] for the existing toDB.";
                     $errors[] = $msg;
            			return $errors;
            		}
      		   }
      		   else {
         			// Creation of the "toUser" failed
                  $msg = "MakeDB - Unable to create the user [$toDBUser] for the existing toDB.";
                  $errors[] = $msg;
         			return $errors;
      		   }
      		}
      		// The root can not select the toDB (so the DB is not present)
      		else {
      		   // Then create the "toDB" using the root connection
      			$errors = MultisitesDatabase::createDatabase( $dbRoot, $DBname);
      			if ( !empty( $errors)) {
         			return $errors;
      			}
      			
      			// Reconnect with the toOptions
         		$dbTo = & MultisitesDatabase::getInstance( $toOptions, 'none');
         		if ( $dbTo->isConnected()) {
         		   // This is OK
         		}
         		else {
                  $msg = "MakeDB - Unable to reconnection with the 'to user' [$toDBUser].";
                  $errors[] = $msg;
         			return $errors;
         		}

         		// If the "toUser" can connect on the "toDB"
         		if ( $dbTo->select_db()) {
         		   // Do nothing
         		}
         		// When the "to User" can not connect on the "to DB"
         		else {
         		   // Try creating of the "to user"
         		   if ( $dbRoot->createUser( $toConfig)) {
         		      // Reconnect the 'toDB'
               		$dbTo = & MultisitesDatabase::getInstance( $toOptions, 'none');
               		if ( $dbTo->isConnected()) {
               		   // This is OK
               		}
               		else {
                        $msg = "MakeDB - Unable connect the new user [$toDBUser].";
                        $errors[] = $msg;
               			return $errors;
               		}
         		   }
         		   else {
            			// Creation of the "toUser" failed
                     $msg = "MakeDB - Unable to grant the user [$toDBUser] for the existing toDB.";
                     $errors[] = $msg;
            			return $errors;
         		   }
         		}
      		}
   		}
   		else {
   		   // Unable to login as root
   			// connection failed
            $msg = 'MakeDB - Unable to login as Root to create the DB dynamically.';
            $errors[] = $msg;
   			return $errors;
   		}
   		
		}
		
		// =========================
		// Starting from here, the "toDB" and the "toUser" should be OK
		// Continue with the old code that create the DB
		// =========================
		
		// if the "to DB" already exists then there is no need to create it.
		$db = & MultisitesDatabase::getDBO($DBtype, $DBhostname, $toDBUser, $toDBPsw, $DBname, $DBPrefix);
// Debug2Win::debug( "makeDB step 0 db is : " . var_export( $db, true));
		if ( JError::isError($db) ) {
		   // try create the DB
//         $msg = 'MakeDB - unable connect with toDB. Error message'. $db->getErrorMsg();
//         $errors[] = $msg;
		}
		else if ($err = $db->getErrorNum()) {
		   // try create the DB
//         $msg = 'MakeDB - Error to connect with toDB. Error number = '.$db->getErrorNum() . ' Error message'. $db->getErrorMsg();
//         $errors[] = $msg;
		}
		else if ( $db->connected()) {
		   // If connection is success, return OK (empty errors)
// Debug2Win::debug( "makeDB is connected step 1 db is : " . var_export( $db, true));
			return $errors;
		}

		// Login to MySQL using the "from DB" user/password or the "root" user/password
		// (do not connect on the DB - just login)
		$DBselect	= false;
		$db = & MultisitesDatabase::getDBO($DBtype, $DBhostname, $DBuserName, $DBpassword, null, $DBPrefix, $DBselect, true);
		
		if ( JError::isError($db) ) {
			// connection failed
         $msg = 'MakeDB - DB Connection fail.' . JText::sprintf('WARNNOTCONNECTDB', $db->toString());
         $errors[] = $msg;
			return $errors;
		}

		if ($err = $db->getErrorNum()) {
			// connection failed
         $msg = "MakeDB - Login MySQL error with user [$DBuserName]. " . JText::_('WARNNOTCONNECTDB') . ' Error number = '.$db->getErrorNum() . ' Error message'. $db->getErrorMsg() ;
         $errors[] = $msg;
         // $msg = "MakeDB - DB Parameters DBtype=[$DBtype], DBhostname=[$DBhostname], DBuserName=[$DBuserName], DBpassword=[$DBpassword]" ;
         $errors[] = $msg;
			return $errors;
		}

		//Check utf8 support of database
		$DButfSupport = $db->hasUTF();
		
		// Try to select the "to DB" using the "From Login"
		if ( ! $db->select($DBname) )
		{
   		// If it is not possible to connect on the "to DB" using the "FROM Login",
   		// Retry connect the DB using the "to DB" user name and password
   		$todb = & MultisitesDatabase::getDBO($DBtype, $DBhostname, $toDBUser, $toDBPsw, null, $DBPrefix, $DBselect);
   		if ( JError::isError( $todb) 
   		  || $todb->getErrorNum()
   		  || ! $todb->select($DBname)
   		   )
   		{
   		   // If it was not possible to connect the "to DB" using the "from DB" user/password or the "to DB" user/password
   		   // Then we assume that the DB does not exist and we try creating the "to DB" using the "from DB" connection

   			$errors = MultisitesDatabase::createDatabase($db, $DBname, $DButfSupport);
   			if ( !empty( $errors)) {
      			return $errors;
   			}
   			
            // If the new DB creation is successfull
      		// Retry connect on the "to DB" using the "to DB" user name and password
      		$todb = & MultisitesDatabase::getDBO($DBtype, $DBhostname, $toDBUser, $toDBPsw, $DBname, $DBPrefix, true, true);
if ( defined( 'MULTISITESDB_DEBUG')) { Debug2Win::debug( "makeDB retry connect DBname=[$DBname] with user=[$toDBUser] / psw=[$toDBPsw] : " . var_export( $todb, true)); }
      		if ( JError::isError( $todb) 
      		  || $todb->getErrorNum()
      		  || ! $todb->select($DBname)
      		   )
      		{
      		   // Ensure that we are connected with the "root" user.
         		$db = & MultisitesDatabase::getDBO($DBtype, $DBhostname, $DBuserName, $DBpassword, null, $DBPrefix, false, true);
         		
      		   // If it was not possible to connection on the to DB with the User Name and Password
         		// then also create the "to DB" user
         		$errors = MultisitesDatabase::_createUser( $db, $toConfig);
if ( defined( 'MULTISITESDB_DEBUG')) { Debug2Win::debug( "makeDB create toDB User / Psw: " . var_export( $toConfig, true) . var_export( $db, true)); }
      			if ( !empty( $errors)) {
         			return $errors;
      			}
      		}
      
			   // and select it as the current DB
//				if ( !$db->select($DBname)) {


			   // Cross-check that we can correctly connect no the new "toDB" with the "to" user information.
      		$db = & MultisitesDatabase::getDBO($DBtype, $DBhostname, $toDBUser, $toDBPsw, $DBname, $DBPrefix, true, true);
      		if ( JError::isError( $db) 
      		  || $db->getErrorNum()
      		  || ! $db->select($DBname)
      		   )
      		{
               $msg = "MakeDB - unable to connect on created DB " . ' Error number = '.$db->getErrorNum() . ' Error message'. $db->getErrorMsg() ;
               $errors[] = $msg;
if ( defined( 'MULTISITESDB_DEBUG')) { Debug2Win::debug( $msg); }
      			return $errors;
				}
   		}
   	// Else the connection on the "to DB" using the "from DB" login is successfull
		} else {

			// pre-existing database - need to set character set to utf8
			// will only affect MySQL 4.1.2 and up
			MultisitesDatabase::setDBCharset($db, $DBname);
			
			// Retry connect the DB using the "to Login" in case where the user would not be created
   		$todb = & MultisitesDatabase::getDBO($DBtype, $DBhostname, $toDBUser, $toDBPsw, null, $DBPrefix, $DBselect);
   		if ( JError::isError( $todb) || $todb->getErrorNum()
   		  || ! $todb->select($DBname)) {
      		// create also the "to DB" user
      		$errors = MultisitesDatabase::_createUser( $db, $toConfig);
   			if ( !empty( $errors)) {
      			return $errors;
   			}
   		}
		}

if ( defined( 'MULTISITESDB_DEBUG')) { Debug2Win::debug_stop( '<< makeDB() - STOP - SUCCESS');}
		// OK the "to DB" exists
		return $errors;
	}

} // End Class

?>