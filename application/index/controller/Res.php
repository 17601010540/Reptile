<?php
namespace app\index\controller;
use think\Db;

class Res
{
	public function list(){
		header("Access-Control-Allow-Origin: *");
		// $result = Db::name('resultlist')->select();
		$result = Db::connect('horseracing_hk')->table('horse_resultlist')->select();
		$arr = array();
		$arr1 = array();
		foreach ($result as $key => $value) {
			//截取场地名称
			$coursename = substr($value['result_name'] , (strpos($value['result_name'] , '-')+2));
			$data = [
				'ccode'       => "HK",
				'courseId'    => $value['id'],
				'coursename'  => $coursename,
				'ymd'         => $value['time'],
			];
			array_push($arr, $data);
		}
		$arr1['data'] = $arr;
		$arr1['status'] = '200';
		return json_encode($arr1);
	}

	public function content(){
		header("Access-Control-Allow-Origin: *");
		$courseId = $_GET['courseId'];
		$raceNumber = $_GET['raceNumber'];
		// $courseId = 6;
		// $raceNumber = 1;

		// $res = Db::name('resultlist')->where("id = $courseId")->select();
		$res = Db::connect('horseracing_hk')->table('horse_resultlist')->where("id = $courseId")->select();
		$ar = explode('-' , $res[0]['time']);
		$key = $ar[2].$ar[1].$ar[0].$raceNumber;
		$where = [
			'resultlistFK' => $courseId,
			'key'    => $key,
		];
		// $result = Db::name('result')->where($where)->select();
		$result = Db::connect('horseracing_hk')->table('horse_result')->where($where)->select();
		// var_dump($result);
		$ar1 = explode('/' , $result[0]['date']);
		$date = $ar1[2].'年'.$ar1[1].'月'.$ar1[0].'日';
		$time = $ar1[2].'-'.$ar1[1].'-'.$ar1[0];
		if(strpos($result[0]['track'] , '-')){
			$track = substr($result[0]['track'] , 0 , (strpos($result[0]['track'] , '-')));
		}else{
			$track = $result[0]['track'];
		}
		
		$data = [
			'raceId'  => $result[0]['id'],
			'courseId'  => $courseId,
			'raceNumber'   => 1,
			'date'         => $date,
			'courseName'   => $result[0]['place'],
 			'time'         => $time,
 			'track'        => $track,
 			'country'      => '香港',
 			'raceTitle'    => $result[0]['name'],
 			'distance'     => $result[0]['distance'],
 			'prizeFundWinner'  => trim($result[0]['money']),
		];

		$keyFK = $result[0]['key'];
 		// $result1 = Db::name('resultinfo')->where("keyFK = $keyFK")->select();
 		$result1 = Db::connect('horseracing_hk')->table('horse_resultinfo')->where("keyFK = $keyFK")->select();
 		//参赛人数
 		$numberOfRunners = count($result1);
 		$data['numberOfRunners'] = $numberOfRunners;
 		$firstTime = $result1[0]['finish_time'];
 		$minute = substr($firstTime , 0 , strpos($firstTime , '.'));
 		$second = substr($firstTime , (strpos($firstTime, '.')+1) , 2);
 		// var_dump($second);
 		$second = $minute*60+$second;
 		$millsecond = substr($firstTime , (strripos($firstTime , '.')+1));
 		$firstTime = $second.'.'.$millsecond;
 		$data['firstTime'] = $firstTime;

 		$arr = array();
 		$arr1 = array();
 		$arr2 = array();
 		foreach ($result1 as $key => $value) {
 			// var_dump($value);
 			$Head = $value['headHorse_distance'];
 			// var_dump($Head);
 			// var_dump(strlen($Head));
 			if(strpos($Head , '-')){
 				if(strlen($Head)==1){
 					$Head = 0;
 					// var_dump($Head);
 				}else{
 					$first = substr($Head , 0 , strpos($Head , '-'));					
 					$final = substr($Head , (strpos($Head , '-')+1));					
 					$fenzi = substr($final , 0 , strpos($final , '/'));
 					$fenmu = substr($final , (strpos($final , '/')+1));
 					$xiaoshu = $fenzi/$fenmu;
 					// var_dump($first);
 					// var_dump($xiaoshu);
 					$Head = $first+$xiaoshu;
 					$Head = number_format($Head , 2 , '.' , '');
 					// var_dump($Head);
 				}
 			}else{
 				if(strpos($Head , '/')){
 					$fenzi = substr($Head , 0 , strpos($Head , '/'));
 					$fenmu = substr($Head , (strpos($Head , '/')+1));
 					$Head = $fenzi/$fenmu;
 					$Head = number_format($Head , 2 , '.' , '');
 					// var_dump($Head);
 				}elseif($Head == '颈位'){
 					$Head = number_format((float)0.20 , 2 , '.' , '');
 					// var_dump($Head);
 				}elseif($Head == '短马头位'){
 					$Head = number_format((float)0.05 , 2 , '.' , '');
 					// var_dump($Head);
 				}elseif($Head == '一头位'){
 					$Head = number_format((float)0.10 , 2 , '.' , '');
 				}elseif($Head == '鼻位'){
 					$Head = number_format((float)0.02 , 2 , '.' , '');
 				}elseif(strlen($Head)==1||strlen($Head)==2||strlen($Head)==3){
 					$Head = number_format((float)$Head , 2 , '.' , '');
 					// var_dump($Head);
 				}
 			}
 			
 			$data1 = [
 				'ranking' => $value['ranking'],
 				'number'  => $value['number'],
 				'horse_name' =>$value['horse_name'],
 				'jockey'     => $value['jockey'],
 				'trainer'    => $value['trainer'],
 				'weightCarried'  => $value['weight'],
 				'gear'           => $value['gear'],
 				'headHorse_distance'  =>$Head,
 				'odds'  => $value['odds'],
 			];

 			array_push($arr, $data1);
 		}
 		
 		$raceNumbers = count(Db::connect('horseracing_hk')->table('horse_result')->where("resultlistFK = $courseId")->select());
 		$arr1['basic'] = $data;
 		$arr1['performances'] = $arr;
 		$arr1['raceNumbers'] = $raceNumbers;
 		$arr2['data'] = $arr1;
 		$arr2['status'] = '200';
 		return json_encode($arr2);
	}
}