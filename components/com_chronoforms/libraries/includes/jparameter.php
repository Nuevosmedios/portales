<?php
/**
* CHRONOFORMS version 4.0
* Copyright (c) 2006 - 2011 Chrono_Man, ChronoEngine.com. All rights reserved.
* Author: Chrono_Man (ChronoEngine.com)
* @license		GNU/GPL
* Visit http://www.ChronoEngine.com for regular updates and information.
**/
defined('_JEXEC') or die('Restricted access');
?>
<?php
	class JParameter{
		var $params = null;
		
		function __construct($string = ''){
			if(is_array($string)){
				$this->params = $string;
			}else{
				$this->setParams($string);
			}
		}
		
		function get($k, $v = null){
			if(array_key_exists($k, $this->params)){
				return $this->params[$k];
			}else{
				return $v;
			}
		}
		
		function set($k, $v){
			$this->params[$k] = $v;
		}
		
		function setParams($string = ''){
			if(strlen(trim(($string))) > 0){
				$data = json_decode($string, true);
				$this->params = $data;
			}else{
				$this->params = array();
			}
		}
		
		function toString(){
			return json_encode($this->params);
		}
		
		function toArray(){
			return $this->params;
		}
		
		function toObject(){
			return json_decode(json_encode($this->params));
		}
	}
?>