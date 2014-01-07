<?php
$action = $_REQUEST["action"];


//use joomla db class
require("../../../../../configuration.php");
$jconfig = new JConfig();
//db establish
$db_error = "I am sorry! We are maintenaning the website, please try again later.";
$db_config = mysql_connect( $jconfig->host, $jconfig->user, $jconfig->password ) or die( $db_error );
mysql_select_db( $jconfig->db, $db_config ) or die( $db_error );
$prefix = $jconfig->dbprefix;
$forumsBaseUrl = "index.php?option=com_fireboard&func=view&id={id}&catid={catid}";
$blogBaseUrl = "index.php?option=com_idoblog&task=viewpost&id={id}";
$wikiBaseUrl = "components/com_joomlawiki/index.php?title=";
$podcastBaseUrl = "index.php?option=com_jukebox&view=category&id={catid}";


function GetPodcast($type,$id=0){
global $prefix;
	if($type == "categories"){
		$queryCategories = "SELECT * FROM ".$prefix."categories WHERE section LIKE 'com_jukebox'";
		$query_execute = mysql_query($queryCategories);
		$categories = array();
		$ic=0;
		while($resultCategories = mysql_fetch_row($query_execute)) {
			   $categories[$ic]=array("Id"=>$resultCategories[0],"Name"=> $resultCategories[2]);
			   $ic++;
		}
		echo json_encode($categories);
	}
	else{
		$query = "SELECT * FROM ".$prefix."jukebox WHERE catid=".$id;
		$qexecute = mysql_query($query);
		$podcasts = array();
		$ij=0;
		while($resultPodcast = mysql_fetch_row($qexecute)) {
		   $podcasts[$ij]=array("Id"=>$resultPodcast[0],"Name"=> $resultPodcast[2]);
		   $ij++;
		}
		echo json_encode($podcasts);
	}
}

function GetForums($type,$catid){
	global $prefix;
	if($type == "categories"){
			$queryCategories = "SELECT * FROM ".$prefix."fb_categories WHERE parent=".$catid;
			$query_execute = mysql_query($queryCategories);
			$categories = array();
			$ic=0;
			while($resultCategories = mysql_fetch_row($query_execute)) {
				   $categories[$ic]=array("Id"=>$resultCategories[0],"Name"=> $resultCategories[2]);
				   $ic++;
			}
			echo json_encode($categories);
	}
	else if($type == "topics"){
		$query = "SELECT * FROM ".$prefix."fb_messages WHERE catid=".$catid." AND parent=0";
		$qexecute = mysql_query($query);
		$topics = array();
		$ij=0;
		while($resultForums = mysql_fetch_row($qexecute)) {
		   $topics[$ij]=array("Id"=>$resultForums[0],"Name"=> $resultForums[7]);
		   $ij++;
		}
		echo json_encode($topics);
	
	}
}

function GetWebcast($type, $catid){
global $prefix;
	if($catid == "0"){
		$catid ="";
	}
	else{
		$catid = $catid."#";
	}


	if($type == "categories"){
		$queryCategories = "SELECT * FROM ".$prefix."seyret_categories WHERE parentcat='".$catid."'";
		$query_execute = mysql_query($queryCategories);
		$categories = array();
		$ic=0;
		while($resultCategories = mysql_fetch_row($query_execute)) {
			   $categories[$ic]=array("Id"=>$resultCategories[4],"Name"=> $resultCategories[1]);
			   $ic++;
		}
		echo json_encode($categories);
	}
	else if($type == "topics"){
		$query = "SELECT * FROM ".$prefix."seyret_items WHERE catid='".$catid."'";
		$qexecute = mysql_query($query);
		$topics = array();
		$ij=0;
		while($resultTopic = mysql_fetch_row($qexecute)) {
		   $topics[$ij]=array("Id"=>$resultTopic[0],"Name"=> $resultTopic[2], "Type"=> $resultTopic[4],"Url"=> $resultTopic[5]);
		   $ij++;
		}
		echo json_encode($topics);
	
	}

}

function GetBlogs($type,$catid){
	global $prefix;
	if($type == "categories"){
			$querySections = "SELECT * FROM ".$prefix."sections WHERE title LIKE 'blogs' AND alias LIKE 'blogs'";
			$qSectsExecute = mysql_query($querySections);
			$resSects = mysql_fetch_row($qSectsExecute);
			
			$queryCategories = "SELECT * FROM ".$prefix."categories WHERE section LIKE '".$resSects[0]."'";
			$query_execute = mysql_query($queryCategories);
			$categories = array();
			$ic=0;
			while($resultCategories = mysql_fetch_row($query_execute)) {
				   $categories[$ic]=array("Id"=>$resultCategories[0],"Name"=> $resultCategories[2]);
				   $ic++;
			}
			echo json_encode($categories);
	}
	else if($type == "topics"){
		$query = "SELECT * FROM ".$prefix."content WHERE catid=".$catid;
		$qexecute = mysql_query($query);
		$topics = array();
		$ij=0;
		while($resultForums = mysql_fetch_row($qexecute)) {
		   $topics[$ij]=array("Id"=>$resultForums[0],"Name"=> $resultForums[1]);
		   $ij++;
		}
		echo json_encode($topics);	
	}
}


function GetWikiAttribute($atributeName){
	global $prefix;
	$q = "SELECT * FROM ".$prefix."kmecertificates_configuration WHERE nombre='".$atributeName."'";
	$qexec = mysql_query($q);
	$res = "";
	while($qres = mysql_fetch_row($qexec)) {
			$res = $qres[1];
	}
	return $res;
}

function GetWiki($type, $category){
	global $prefix, $db_config, $db_error;
	if($type == "categories"){
		$wikiCategories = array("A","B","C","D","E","F","G","H","I","J","K","L","M","N","O","P","Q","R","S","T","U","V","W","X","Y","Z");
		$categories = array();
		$ic = 0;
		foreach($wikiCategories as $indexC => $valueC){
			$categories[$ic]=array("Id"=>$valueC,"Name"=> $valueC);
			$ic++;
		}
		echo json_encode($categories);
	}
	else if($type == "topics"){
		$wHost = GetWikiAttribute("WikiHost");
		$wUser = GetWikiAttribute("WikiUser");
		$wPassword = GetWikiAttribute("WikiPassword");
		$wDBName = GetWikiAttribute("WikiBDName");
		$wPrefix = GetWikiAttribute("WikiPrefix");
		mysql_close($db_config);
		$db_config = mysql_connect( $wHOst, $wUser, $wPassword ) or die( $db_error );
		mysql_select_db( $wDBName, $db_config ) or die( $db_error );

		$q = "SELECT * FROM ".$wPrefix."wiki_page WHERE page_title LIKE '".$category."%'";
		$qexecute = mysql_query($q);
		$topics = array();
		$ij=0;
		while($resultWiki = mysql_fetch_row($qexecute)) {
		   $topics[$ij]=array("Id"=>$resultWiki[0],"Name"=> str_replace("_"," ",$resultWiki[2]));
		   $ij++;
		}
		echo json_encode($topics);			
	}		
}


function GetCourses(){
	global $prefix;
	
	$queryCourses = "SELECT * FROM ".$prefix."kmecertificates";
	$qCoursesExecute = mysql_query($queryCourses);
	$courses =  array();
	$ic=0;
	while($resCourses = mysql_fetch_row($qCoursesExecute)) {
		   $courses[$ic] = $resCourses[0];
		   $ic++;
	}
	echo json_encode($courses);

}

function GetCourse($code){
	global $prefix;
	
	$queryCourse = "SELECT * FROM ".$prefix."kmecertificates WHERE curso_kme LIKE '".$code."'";
	$qCourseExecute = mysql_query($queryCourse);
	$course =  array();
	if($resCourse = mysql_fetch_row($qCourseExecute)) {
		   $course["code"] = $resCourse[0];
		   $course["name"] = $resCourse[1];
		   $course["html"] = $resCourse[2];
		   $course["resources"] = array("forums"=>array("name"=>"lang.Forums","items"=>array()),"podcast"=>array("name"=>"lang.Podcast","items"=>array()),"webcast"=>array("name"=>"lang.Webcast","items"=>array()),"blogs"=>array("name"=>"lang.Blogs","items"=>array()),"wiki"=>array("name"=>"lang.Wiki","items"=>array()));
     	   $query = "SELECT * FROM ".$prefix."kmecertificates_resources WHERE id_curso LIKE '".$code."'";
		   $queryExecute = mysql_query($query);
		   while($res = mysql_fetch_row($queryExecute)){
			   $course["resources"][$res[2]]["items"][count($course["resources"][$res[2]]["items"])] = array("Id" => $res[1],"Name"=>$res[3],"CatId"=>$res[5],"Url"=>$res[4]);
		   }
	}	

	echo json_encode($course);

}

function SavePage($page){
	global $prefix, $forumsBaseUrl, $blogBaseUrl, $wikiBaseUrl, $podcastBaseUrl;
	$qdel = "DELETE FROM ".$prefix."kmecertificates WHERE curso_kme like '".$page["code"]."'";
	mysql_query($qdel);
	$qrdel = "DELETE FROM ".$prefix."kmecertificates_resources WHERE id_curso LIKE '".$page["code"]."'";
	mysql_query($qrdel);
	$querykmecert = "INSERT INTO ".$prefix."kmecertificates (curso_kme,nombre_curso,plan_de_estudios) values('".$page["code"]."','".$page["name"]."','".$page["html"]."')";
	$querykmecertres = mysql_query($querykmecert);
	$resources = $page["resources"];
	foreach($resources as $resource => $value){
		if(count($value) > 1)
		    $list = $value["items"];
		else
			$list = array();
	    foreach($list as $index => $item){
			$resourceUrl = "";
			switch($resource){
				case "forums":			
				$resourceUrl = str_replace("{id}",$item["Id"],str_replace("{catid}",$item["CatId"],$forumsBaseUrl));
				break;
				case "blogs":
				$resourceUrl = str_replace("{id}",$item["Id"],str_replace("{catid}",$item["CatId"],$blogBaseUrl));
				break;
				case "webcast":
				$resourceUrl = $item["Url"];
				break;
				case "wiki":
				$resourceUrl = $wikiBaseUrl.str_replace(" ","_",$item["Name"]);
				break;
				case "podcast":
				$resourceUrl = str_replace("{catid}",$item["CatId"],$podcastBaseUrl);
				break;
				default:
				$resourceUrl ="";
				break;
			}
			$query = "INSERT INTO ".$prefix."kmecertificates_resources (id_curso,id_recurso, recurso, nombre, url,categoria_recurso) values('".$page["code"]."','".$item["Id"]."','".$resource."','".$item["Name"]."','".$resourceUrl."','".$item["CatId"]."')";
			mysql_query($query);
		}
	}
	echo json_encode(true);
}

function SaveConfiguration($configuration){
global $prefix;
	foreach($configuration as $config => $value){
		$qdel = "DELETE FROM ".$prefix."kmecertificates_configuration WHERE nombre='".$config."'";
		mysql_query($qdel);
		$query = "INSERT INTO ".$prefix."kmecertificates_configuration (nombre, valor) values('".$config."','".$value."')";
		mysql_query($query);
	}
	echo json_encode(true);
}


function ConsumeResource($user, $resource, $code, $catid, $id){
global $prefix;
	$qdel = "DELETE FROM ".$prefix."kmecertificates_consumedresources WHERE usuario like '".$user."' AND id_recurso = '".$id."' AND recurso LIKE '".$resource."' AND id_curso LIKE '".$code."' AND id_categoria ='".$catid."'";
	mysql_query($qdel);
	$query = "INSERT INTO ".$prefix."kmecertificates_consumedresources (usuario,recurso,id_recurso, id_curso, id_categoria, estado) values('".$user."','".$resource."','".$id."','".$code."','".$catid."','1')";
	mysql_query($query);
	echo json_encode(true);
}
function GetCompletedPercentage($code,$user){
global $prefix;
	$queryTotalCourse = "SELECT COUNT(*) FROM ".$prefix."kmecertificates_resources WHERE id_curso LIKE '".$code."'";
	$qTotalCourseExecute = mysql_query($queryTotalCourse);
	$resTotalCourse = mysql_fetch_row($qTotalCourseExecute);
	$queryCourse = "SELECT COUNT(*) FROM ".$prefix."kmecertificates_consumedresources WHERE id_curso LIKE '".$code."' AND usuario LIKE '".$user."'";
	$qCourseExecute = mysql_query($queryCourse);
	$resCourse = mysql_fetch_row($qCourseExecute);
	$response = 0;
		if($resTotalCourse[0]+1 > 0){
		$response = ($resCourse[0]/($resTotalCourse[0]+1)) * 100;
	}
	echo json_encode($response);
}

function GetConfiguration(){
global $prefix;
	$query = "SElECT * FROM ".$prefix."kmecertificates_configuration";
    $queryExecute = mysql_query($query);
	$cofiguration = array();
   	while($res = mysql_fetch_row($queryExecute)){
		$configuration[$res[0]]=$res[1];
   }	
   echo json_encode($configuration);
}

switch($action){
case "get":
	$resource = $_REQUEST["resource"];
	$type = $_REQUEST["type"];
	$catid = $_REQUEST["catid"];
	$code = $_REQUEST["code"];
	switch($resource){
		case "podcast":
			GetPodcast($type,$catid);
		break;
		case "forums":
			GetForums($type,$catid);
		break;
		case "blogs":
			GetBlogs($type,$catid);
		break;
		case "webcast":
			GetWebcast($type,$catid);
		break;
		case "wiki":
			GetWiki($type, $catid);
		break;
		case "configuration":
			GetConfiguration();
			break;
		default:
		if($code == null)
			GetCourses();
		else
			GetCourse($code);
		break;
	}
	break;
case "save":
	if($_REQUEST["page"]){
		SavePage($_REQUEST["page"]);		
		}
	else{
		SaveConfiguration($_REQUEST["configuration"]);
		}
break;
case "click":
$resource = $_REQUEST["resource"];
$user = $_REQUEST["user"];
$code = $_REQUEST["code"];
$catid = $_REQUEST["catid"];
$id = $_REQUEST["id"];
	ConsumeResource($user, $resource, $code, $catid, $id);
break;
case "getCompletedPercentage":
$user = $_REQUEST["user"];
$code = $_REQUEST["code"];
	GetCompletedPercentage($code,$user);
default:
echo "";
break;

}
mysql_close($db_config);//andy:close db for security reason
?>