<?php 
	require ("../../configuration.php");
	$jconfig = new JConfig();
	$db_config = mysql_connect( $jconfig->host, $jconfig->user, $jconfig->password ) or die( $db_error );
	mysql_select_db( $jconfig->db, $db_config ) or die( $db_error );
	$prefix = $jconfig->dbprefix; 

	$action = $_GET['action'];
	$pid = $_GET['pid'];
	$cid = $_GET['cid'];
	$uid = $_GET['uid'];
	$points = $_GET['points'];
	
	switch($action){
		//CRUD PRODUCT
		case "getAllProducts":
			getAllProducts();
		break;
		case "getAllProductsByBrand":
			getAllProductsByBrand();
		break;
		case "getPerCategoryProducts":
			getPerCategoryProducts($cid);
		break;
		case "getProduct":
			getProduct($pid);
		break;
		case "productHasCoupon":
			productHasCoupon($pid);
		break;
		case "getProductCoupons":
			getProductCoupons($pid);
		break;
		case "getUserLoyaltyPoints":
			getUserLoyaltyPoints($uid);
		break;
		case "setUserLoyaltyPoints":
			setUserLoyaltyPoints($uid,$points);
		break;
	}
	
	function setUserLoyaltyPoints($uid,$points){
		global $prefix;
		$query="UPDATE aloyalty_points SET points=".$points."  WHERE id_user=".$uid;
		$exec = mysql_query($query);
		$data = array();
		if(mysql_query($query)){
			/*$ic = 0;
			while($result = mysql_fetch_row($exec)){
				$data[$ic]=array(
					"id"=>$result[0],
					"id_user"=>$result[1],
					"points"=>$result[2]
				);
				$ic ++;
			}*/
		}
		echo json_encode($data);
	}
	
	function getUserLoyaltyPoints($uid){
		global $prefix;
		$query="SELECT * FROM aloyalty_points WHERE id_user=".$uid;
		//echo $query;
		$exec = mysql_query($query);
		$data = array();
		if(mysql_query($query)){
			$ic = 0;
			while($result = mysql_fetch_row($exec)){
				$data[$ic]=array(
					"id"=>$result[0],
					"id_user"=>$result[1],
					"points"=>$result[2]
				);
				$ic ++;
			}
		}
		echo json_encode($data);
	}
	
	function getProductCoupons($pid){
		global $prefix;
		$query="SELECT * FROM acupon WHERE id_product=".$pid;
		//echo $query;
		$exec = mysql_query($query);
		$data = array();
		if(mysql_query($query)){
			$ic = 0;
			while($result = mysql_fetch_row($exec)){
				$data[$ic]=array(
					"id"=>$result[0],
					"id_product"=>$result[1],
					"name"=>$result[2],
					"description"=>$result[3],
					"discount"=>$result[4],
					"id_cat"=>$result[5],
					"code"=>$result[6],
					"id_acoupon_type"=>$result[7]
				);
				$ic ++;
			}
		}
		echo json_encode($data);
	}
	
	function productHasCoupon($pid){
		global $prefix;
		$query="SELECT COUNT(*) FROM acupon WHERE id_product=".$pid;
		//echo $query;
		$exec = mysql_query($query);
		$data = array();
		if(mysql_query($query)){
			$result = mysql_fetch_row($exec);
			$data[0]=array("coupons"=>$result[0]);
		}
		echo json_encode($data);
	}
	
	function getAllProducts(){
		global $prefix;
		$query="SELECT p.id,c.name,p.name,p.description,p.price,p.inv,p.img,b.name FROM aproduct as p, acat as c, abrand as b WHERE p.id_cat=c.id AND p.id_brand=b.id";
		$exec = mysql_query($query);
		$data = array();
		if(mysql_query($query)){
			$ic = 0;
			while($result = mysql_fetch_row($exec)){
				$data[$ic]=array(
					"id"=>$result[0],
					"cat"=>$result[1],
					"name"=>$result[2],
					"description"=>$result[3],
					"price"=>$result[4],
					"inv"=>$result[5],
					"img"=>$result[6],
					"brand"=>$result[7]
				);
				$ic ++;
			}
		}
		echo json_encode($data);
	}
	function getAllProductsByBrand(){
		global $prefix;
		$query="SELECT p.id,c.name,p.name,p.description,p.price,p.inv,p.img,b.name FROM aproduct as p, acat as c, abrand as b WHERE p.id_cat=c.id AND p.id_brand=b.id ORDER BY b.name ASC";
		$exec = mysql_query($query);
		$data = array();
		if(mysql_query($query)){
			$ic = 0;
			while($result = mysql_fetch_row($exec)){
				$data[$ic]=array(
					"id"=>$result[0],
					"cat"=>$result[1],
					"name"=>$result[2],
					"description"=>$result[3],
					"price"=>$result[4],
					"inv"=>$result[5],
					"img"=>$result[6],
					"brand"=>$result[7]
				);
				$ic ++;
			}
		}
		echo json_encode($data);
	}
?>