<?php
namespace app\index\controller;
use think\Db;

class Res
{
	public function list(){
		// header("Access-Control-Allow-Origin: http://192.168.1.13");
		header("Access-Control-Allow-Origin: *");
		$result = Db::name('resultlist')->select();
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
		// header("Access-Control-Allow-Origin: http://192.168.1.13");
		header("Access-Control-Allow-Origin: *");
		$courseId = $_GET['courseId'];
		$raceNumber = $_GET['raceNumber'];
		// $courseId = 1;
		// $raceNumber = 1;

		$res = Db::name('resultlist')->where("id = $courseId")->select();
		$ar = explode('-' , $res[0]['time']);
		$key = $ar[2].$ar[1].$ar[0].$raceNumber;
		$where = [
			'resultlistFK' => $courseId,
			'key'    => $key,
		];
		$result = Db::name('result')->where($where)->select();
		// var_dump($result);
		$ar1 = explode('/' , $result[0]['date']);
		$date = $ar1[2].'年'.$ar1[1].'月'.$ar1[0].'日';
		$time = $ar1[2].'-'.$ar1[1].'-'.$ar1[0].'-';
		$track = substr($result[0]['track'] , 0 , (strpos($result[0]['track'] , ' ')));
		
		$data = [
			'raceId'  => $result[0]['id'],
			'courseId'  => $courseId,
			'raceNumber'   => $raceNumber,
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
 		$result1 = Db::name('resultinfo')->where("keyFK = $keyFK")->select();
 		//参赛人数
 		$numberOfRunners = count($result1);
 		$data['numberOfRunners'] = $numberOfRunners;

 		$arr = array();
 		$arr1 = array();
 		$arr2 = array();
 		foreach ($result1 as $key => $value) {
 			// var_dump($value);
 			$data1 = [
 				'ranking' => $value['ranking'],
 				'number'  => $value['number'],
 				'horse_name' =>$value['horse_name'],
 				'jockey'     => $value['jockey'],
 				'trainer'    => $value['trainer'],
 				'weightCarried'  => $value['weight'],
 				'gear'           => $value['gear'],
 				'headHorse_distance'  =>$value['headHorse_distance'],
 				'odds'  => $value['odds'],
 			];

 			array_push($arr, $data1);
 		}
 		
 		$raceNumbers = count(Db::name('result')->where("resultlistFK = $courseId")->select());
 		$arr1['basic'] = $data;
 		$arr1['performances'] = $arr;
 		$arr1['raceNumbers'] = $raceNumbers;
 		$arr2['data'] = $arr1;
 		$arr2['status'] = '200';
 		return json_encode($arr2);
	}
}