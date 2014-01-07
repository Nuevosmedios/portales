<?php
/**
 * @version $Id: tool_pretend.php
 * @package AEC - Account Control Expiration - Membership Manager
 * @subpackage Toolbox - Pretend
 * @copyright 2011-2012 Copyright (C) David Deutsch
 * @author David Deutsch <skore@valanx.org> & Team AEC - http://www.valanx.org
 * @license GNU/GPL v.3 http://www.gnu.org/licenses/gpl.html or, at your option, any later version
 */

// Dont allow direct linking
( defined('_JEXEC') || defined( '_VALID_MOS' ) ) or die( 'Direct Access to this location is not allowed.' );

class tool_pretend
{
	function Info()
	{
		$info = array();
		$info['name'] = "Pretend";
		$info['desc'] = "Create artificial membership and sales data.";

		return $info;
	}

	function Settings()
	{
		$settings = array();
		$settings['create_groups']		= array( 'toggle', 'Create Groups', 'Create new groups (or just use the ones already in the system)', 0 );
		$settings['groups']				= array( 'inputB', 'Groups', 'Number of Membership Plan Groups', 3 );
		$settings['create_plans']		= array( 'toggle', 'Create Plans', 'Create new plans (or just use the ones already in the system)', 0 );
		$settings['plans']				= array( 'inputB', 'Plans', 'Number of Membership Plans', 10 );
		$settings['create_users']		= array( 'toggle', 'Create Users', 'Create new users (or just use the ones already in the system)', 0 );
		$settings['users']				= array( 'inputB', 'Users', 'Number of Users', 25000 );
		$settings['mincost']			= array( 'inputB', 'Min Cost', 'Plans will have a random cost assigned, with this as the smallest possible amount', '5.00' );
		$settings['maxcost']			= array( 'inputB', 'Max Cost', 'Plans will have a random cost assigned, with this as the largest possible amount', '100.00' );
		$settings['create_payments']	= array( 'toggle', 'Create Payments', 'Create new payments', 0 );
		$settings['start']				= array( 'inputB', 'Start', 'Start of business (Year)', date('Y')-6 );

		return $settings;
	}

	function Action()
	{
		if ( empty( $_POST['groups'] ) ) {
			return null;
		}

		// Create a number of groups
		$grouplist = $this->createGroups( $_POST['groups'] );
		
		// Create a number of plans
		$this->createPlans( $grouplist, $_POST['plans'] );

		// Create Users
		$this->createUsers( $_POST['users'] );

		// Create Payments
		$this->createPayments();

		$h = 0;

		// Store some data so we can delete fake entries later on, if needed
		if ( $_POST['create_users'] ) {
			$h++;
			$data['range_users'] = array( $this->range['users']['start'], $this->range['users']['end'] );
		} else {
			$data['range_users'] = array( 0, 0 );
		}

		if ( $_POST['create_plans'] ) {
			$h++;
			$data['range_plans'] = array( $this->range['plans']['start'], $this->range['plans']['end'] );
		} else {
			$data['range_plans'] = array( 0, 0 );
		}

		if ( $_POST['create_groups'] ) {
			$h++;
			$data['range_groups'] = array( $this->range['groups']['start'], $this->range['groups']['end'] );
		} else {
			$data['range_groups'] = array( 0, 0 );
		}

		if ( $_POST['create_payments'] ) {
			$h++;
			$data['range_payments'] = array( $this->range['payments']['start'], $this->range['payments']['end'] );
		} else {
			$data['range_payments'] = array( 0, 0 );
		}

		if ( $h ) {
			$db = &JFactory::getDBO();

			$bucket = new aecBucket();
			$bucket->stuff( 'tool_pretend', $data );

			return "<p>Alright!</p>";
		} else {
			return "<p>Seems like nothing happened...</p>";
		}
	}

	function createPayments()
	{
		if ( !$_POST['create_payments'] ) {
			return;
		}

		$db = &JFactory::getDBO();

		$amountlist = array();

		for( $i=$this->range['plans']['start']; $i<=$this->range['plans']['end']; $i++ ) {
			$amountlist[$i] = array();

			$modlist[$i]['sin'] = rand(0, 360);
			$modlist[$i]['speed'] = rand(0, 1000)/100;
			$modlist[$i]['multi'] = rand(0, 100)/50;
		}

		$start_date = strtotime( $_POST['start'].'-01-01 00:00:00' );

		$years = (int) date('Y')-$_POST['start'];

		$days = ($years*365)+date('z')+1;

		$saleslist = $this->stream_layers( $amountlist, $days+2, 1 );

		$plandetails = array();

		$query = 'SELECT MIN(id)'
				. ' FROM #__acctexp_log_history'
				;
		$db->setQuery( $query );

		$this->range['payments']['start'] = $db->loadResult()+1;

		for( $i=1; $i<=$days; $i++ ) {
			$dtime = strtotime( "+".$i." days", $start_date );

			foreach ( $modlist as $k => $v ) {
				$modlist[$k]['sin'] += $modlist[$k]['speed'];

				$modlist[$k]['sin'] = $modlist[$k]%360;
			}

			foreach ( $saleslist as $plan => $dayslist ) {
				if ( !isset( $plandetails[$plan] ) ) {
					$splan = new SubscriptionPlan();
					$splan->load( $plan );

					$plandetails[$plan] = array( 'name' => $splan->name, 'cost' => $splan->params['full_amount'] );
				}

				// Add some sine modification
				$dsales = (int) ( $dayslist[$i] * ( ( 1 + sin( $modlist[$plan]['sin'] ) ) * $modlist[$plan]['multi'] ) );

				// Less sales on the weekends
				if ( date('N',$dtime) > 5 ) {
					$dsales = (int) ( $dsales/rand(1,4) );
				}

				for( $j=0; $j<$dsales; $j++ ) {
					$log = new logHistory();

					$userid = rand($this->range['users']['start'], $this->range['users']['end']);

					$log->plan_id				= $plan;
					$log->plan_name				= $plandetails[$plan]['name'];
					$log->proc_id				= 0;
					$log->proc_name				= 'none';
					$log->user_id				= $userid;
					$log->user_name				= '';
					$log->transaction_date		= date( 'Y-m-d H:i:s', $dtime+rand(0,86400) );
					$log->amount				= $plandetails[$plan]['cost'];
					$log->invoice_number		= "";
					$log->response				= "";

					$log->check();
					$log->store();
				}
			}
		}

		$query = 'SELECT MAX(id)'
				. ' FROM #__acctexp_log_history'
				;
		$db->setQuery( $query );

		$this->range['payments']['end'] = $db->loadResult();
	}

	function createPlans( $grouplist, $plans )
	{
		$db = &JFactory::getDBO();

		if ( !$_POST['create_plans'] ) {
			$query = 'SELECT MIN(id)'
					. ' FROM #__acctexp_plans'
					;
			$db->setQuery( $query );

			$this->range['plans']['start'] = $db->loadResult();

			$query = 'SELECT MAX(id)'
					. ' FROM #__acctexp_plans'
					;
			$db->setQuery( $query );

			$this->range['plans']['end'] = $db->loadResult();

			return;
		}

		$class = array( 'diamond', 'copper', 'silver', 'titanium', 'gold', 'platinum' );
		$color = array( '', 'azure ', 'scarlet ', 'jade ', 'lavender ', 'mustard ' );

		$pricerange = array( ( (int) $_POST['mincost']*100 ), ( (int) $_POST['maxcost']*100 ) );

		for ( $i=1; $i<=$plans; $i++ ) {
			if ( isset( $color[floor($i/6)] ) && isset( $class[$i%6] ) ) {
				$name = ucfirst( $color[floor($i/6)] ) . ucfirst( $class[$i%6] ) . " Plan";
			} else {
				$name = "Plan " . $i;
			}

			$row = new SubscriptionPlan();

			$post = array(	'active' => 1,
							'visible' => 0,
							'name' => $name,
							'desc' => "Buy " . $name . " now!",
							'processors' => '',
							'full_amount' => round( rand( $pricerange[0], $pricerange[1] ) / 100, 2 ),
							'full_free' => 0,
							'trial_free' => 0,
							'trial_amount' => 0
			);

			$row->savePOSTsettings( $post );
			$row->storeload();

			ItemGroupHandler::setChildren( rand($this->range['groups']['start'], $this->range['groups']['end']), array($row->id), $type='item' );

			if ( $i == 1 ) {
				$this->range['plans']['start'] = $row->id;
			} elseif ( $i == $plans ) {
				$this->range['plans']['end'] = $row->id;
			}
		}
	}

	function createGroups( $amount )
	{
		$db = &JFactory::getDBO();

		if ( !$_POST['create_groups'] ) {
			$query = 'SELECT MIN(id)'
					. ' FROM #__acctexp_itemgroups'
					. ' WHERE `id` > 1'
					;
			$db->setQuery( $query );

			$this->range['groups']['start'] = $db->loadResult();

			$query = 'SELECT MAX(id)'
					. ' FROM #__acctexp_itemgroups'
					;
			$db->setQuery( $query );

			$this->range['groups']['end'] = $db->loadResult();

			return;
		}

		$colors = array(	'1f77b4', 'aec7e8', 'ff7f0e', 'ffbb78', '2ca02c', '98df8a', 'd62728', 'ff9896',
							'9467bd', 'c5b0d5', '8c564b', 'c49c94', 'e377c2', 'f7b6d2', '7f7f7f', 'c7c7c7',
							'bcbd22', 'dbdb8d', '17becf', '9edae5', 'BBDDFF', '5F8BC4', 'A2BE72', 'DDFF99',
							'D07C30', 'C43C42', 'AA89BB', 'B7B7B7', '808080' );

		$grouplist = array();
		for ( $i=0; $i<=$amount; $i++ ) {
			$row = new ItemGroup();

			$post = array(
							'active' => 1,
							'visible' => 0,
							'name' => 'Group '.($i+1),
							'desc' => 'Group '.($i+1),
							'color' => $colors[$i%29]
			);

			$row->savePOSTsettings( $post );
			$row->storeload();

			ItemGroupHandler::setChildren( 1, array($row->id), $type='group' );

			if ( $i == 0 ) {
				$this->range['groups']['start'] = $row->id;
			} elseif ( $i == $amount ) {
				$this->range['groups']['end'] = $row->id;
			}

			$grouplist[] = $row->id;
		}

		return $grouplist;
	}

	function createUsers( $amount )
	{
		$db = &JFactory::getDBO();

		if ( !$_POST['create_users'] ) {
			$query = 'SELECT MIN(id)'
					. ' FROM #__users'
					. ' WHERE `id` > 64'
					;
			$db->setQuery( $query );

			$this->range['users']['start'] = $db->loadResult();

			$query = 'SELECT MAX(id)'
					. ' FROM #__users'
					;
			$db->setQuery( $query );

			$this->range['users']['end'] = $db->loadResult();

			return;
		}

		for ( $i=0; $i<=$amount; $i++ ) {
			$rname = $this->getRandomName();

			$var = array(	'username' => $this->generateUsername( $rname ),
							'password' => 'password',
							'password2' => 'password',
							'email' => $this->generateEmail( $rname ),
							'name' => $rname,
							);

			$userid = AECToolbox::saveUserRegistration( 'com_acctexp', $var, true, true, true, true );

			if ( $i == 0 ) {
				$this->range['users']['start'] = $userid;
			} elseif ( $i == $amount ) {
				$this->range['users']['end'] = $userid;
			}
		}
	}

	function getRandomName()
	{
		static $male = array(	"John","William","James","George","Charles",
						"Frank","Joseph","Henry","Robert","Thomas",
						"Edward","Harry","Walter","Arthur","Fred",
						"Albert","Samuel","Clarence","Louis","David",
						"Joe","Charlie","Richard","Ernest","Roy",
						"Will","Andrew","Jesse","Oscar","Willie",
						"Daniel","Benjamin","Carl","Sam","Alfred",
						"Earl","Peter","Elmer","Frederick","Howard",
						"Lewis","Ralph","Herbert","Paul","Lee",
						"Tom","Herman","Martin","Jacob","Michael",
						"Jim","Claude","Ben","Eugene","Francis",
						"Grover","Raymond","Harvey","Clyde","Edwin",
						"Edgar","Ed","Lawrence","Bert","Chester",
						"Jack","Otto","Luther","Charley","Guy",
						"Floyd","Ira","Ray","Hugh","Isaac",
						"Oliver","Patrick","Homer","Theodore","Leonard",
						"Leo","Alexander","August","Harold","Allen",
						"Jessie","Archie","Philip","Stephen","Horace",
						"Marion","Bernard","Anthony","Julius","Warren",
						"Leroy","Clifford","Eddie","Sidney","Milton");

		static $female = array("Mary","Anna","Emma","Elizabeth","Margaret",
						"Minnie","Ida","Bertha","Clara","Alice",
						"Annie","Florence","Bessie","Grace","Ethel",
						"Sarah","Ella","Martha","Nellie","Mabel",
						"Laura","Carrie","Cora","Helen","Maude",
						"Lillian","Gertrude","Rose","Edna","Pearl",
						"Edith","Jennie","Hattie","Mattie","Eva",
						"Julia","Myrtle","Louise","Lillie","Jessie",
						"Frances","Catherine","Lula","Lena","Marie",
						"Ada","Josephine","Fannie","Lucy","Dora",
						"Agnes","Maggie","Blanche","Katherine","Elsie",
						"Nora","Mamie","Rosa","Stella","Daisy",
						"May","Effie","Mae","Ellen","Nettie",
						"Ruth","Alma","Della","Lizzie","Sadie",
						"Sallie","Nancy","Susie","Maud","Flora",
						"Irene","Etta","Katie","Lydia","Lottie",
						"Viola","Caroline","Addie","Hazel","Georgia",
						"Esther","Mollie","Olive","Willie","Harriet",
						"Emily","Charlotte","Amanda","Kathryn","Lulu",
						"Susan","Kate","Nannie","Jane","Amelia");

		static $surnames = array("Smith","Johnson","Williams","Brown","Jones",
						"Miller","Davis","Garcia","Rodriguez","Wilson",
						"Martinez","Anderson","Taylor","Thomas","Hernandez",
						"Moore","Martin","Jackson","Thompson","White",
						"Lopez","Lee","Gonzalez","Harris","Clark",
						"Lewis","Robinson","Walker","Perez","Hall",
						"Young","Allen","Sanchez","Wright","King",
						"Scott","Green","Baker","Adams","Nelson",
						"Hill","Ramirez","Campbell","Mitchell","Roberts",
						"Carter","Phillips","Evans","Turner","Torres",
						"Parker","Collins","Edwards","Stewart","Flores",
						"Morris","Nguyen","Murphy","Rivera","Cook",
						"Rogers","Morgan","Peterson","Cooper","Reed",
						"Bailey","Bell","Gomez","Kelly","Howard",
						"Ward","Cox","Diaz","Richardson","Wood",
						"Watson","Brooks","Bennett","Gray","James",
						"Reyes","Cruz","Hughes","Price","Myers",
						"Long","Foster","Sanders","Ross","Morales",
						"Powell","Sullivan","Russell","Ortiz","Jenkins",
						"Gutierrez","Perry","Butler","Barnes","Fisher");

		// Feminist code is feminist
		$gender = rand(0, 1);

		$sno = rand(1, 4);

		$name = array();
		for ( $j=0; $j<$sno; $j++ ) {
			$rname = rand(0, 99);

			if ( $gender ) {
				$name[] = $female[$rname];
			} else {
				$name[] = $male[$rname];
			}
		}

		$rname = rand(0, 99);

		$name[] = $surnames[$rname];

		return implode( " ", $name );
	}

	function generateUsername( $seed )
	{
		$db = &JFactory::getDBO();

		$numberofrows	= 1;
		while ( $numberofrows ) {
			$username = strtolower( str_replace( " ", "", $seed )  );

			$query = 'SELECT count(*)'
					. ' FROM #__users'
					. ' WHERE LOWER( `username` ) = \'%' . $username . '%\''
					;
			$db->setQuery( $query );
			$numberofrows = $db->loadResult();
		}

		return $username;
	}

	function generateEmail( $seed )
	{
		$db = &JFactory::getDBO();

		$domains = array(	'yahoo.com','hotmail.com','aol.com','gmail.com','msn.com',
							'comcast.net','hotmail.co.uk','sbcglobal.net','yahoo.co.uk','yahoo.co.in',
							'bellsouth.net','verizon.net','earthlink.net','cox.net','rediffmail.com',
							'yahoo.ca','btinternet.com','charter.net','shaw.ca','ntlworld.com' );

		$numberofrows	= 1;
		while ( $numberofrows ) {
			$email = strtolower( str_replace( " ", rand(11111, 99999), $seed )  ) . rand(11, 999) . '@' . $domains[rand(0, 19)];

			$query = 'SELECT count(*)'
					. ' FROM #__users'
					. ' WHERE LOWER( `email` ) = \'%' . $email . '%\''
					;
			$db->setQuery( $query );
			$numberofrows = $db->loadResult();
		}

		return $email;
	}

	/* Inspired by d3 examples, in turn inspired by Lee Byron's test data generator. */
	function stream_layers( $layers, $samples, $step )
	{
		foreach ( $layers as $lid => $lc ) {
			$a = array();

			for ( $i=0; $i<$samples; $i++ ) {
				$a[$i] = $step + $step * rand();
			}

			for ( $i=0; $i<5; $i++) {
				$a = $this->bump( $a, $samples );
			}

			foreach ( $a as $k => $v ) {
				$a[$k] = (int) ($v/((rand(15,65)*10000000)));
			}

			$layers[$lid] = $a;
		}

		return $layers;
	}

	function bump( $array, $samples )
	{
		$x = 1 / (.1 + rand() );
		$y = 2 * rand() - 0.5;
		$z = 10 / (0.1 + rand());

		for ( $i=0; $i<$samples; $i++ ) {
			$w = ($i / $samples - $y) * $z;
			$array[$i] += $x * exp(-$w * $w);
		}

		return $array;
	}
}
?>
