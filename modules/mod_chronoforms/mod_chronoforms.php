<?php
// no direct access
defined('_JEXEC') or die('Restricted access');
if(!defined('DS')){
	define('DS', DIRECTORY_SEPARATOR);
}
if(!class_exists('JCFParameter')){
	class JCFParameter{
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
}
// Include the whosonline functions only once
require_once (dirname(__FILE__).DS.'helper.php');

$formname = $params->get('chronoform', '');
echo $formcode = modChronoFormsHelper::_displayForm($formname);
?>