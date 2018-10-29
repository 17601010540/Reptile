<?php
namespace app\index\controller;
require_once 'utf_t2s.php';
// header('Content-Type:text/html;charset=utf-8'); ////防止json格式的中文乱码输出
        
$GLOBALS['s2t_table'] = array_flip($GLOBALS['t2s_table']);

 
class Trans{
	public $location;
	public function utf8_ts($location){
		$this->location=$location;
	}
 
	// function utf8_s2t($word){
	// 	return strtr($word,$GLOBALS['s2t_table']);
	// }
 
	function utf8_t2s($word){
		return strtr($word,$GLOBALS['t2s_table']);
	}
}
?>
