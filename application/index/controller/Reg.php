<?php
namespace app\index\controller;
use think\Db;

class Reg
{
	public function list(){
		
		header("Access-Control-Allow-Origin: *");
		// $result = Db::name('ranklist')->select();
		$result = Db::connect('horseracing_hk')->table('horse_ranklist')->select();
		$arr = array();
		$arr1 = array();
		$arr2 = array();
		$time = date('Y-m-d' , time());
		$time = str_replace('-' , '' , $time);
		foreach ($result as $key => $value) {
			$time1 = str_replace('-' , '' , $value['time']);
			if ($time<=$time1){
				array_push($arr2, $result[$key]);
			}

			
		}
		foreach ($arr2 as $key => $value) {
			//截取场地名称
			$coursename = substr($value['rank_name'] , (strpos($value['rank_name'] , '-')+2));
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
		// $courseId = 6;
		// $raceNumber = 1;

		$arr = array();
		$arr1 = array();
		$arr2 = array();


		// $res = Db::name('ranklist')->where("id = $courseId")->select();
		$res = Db::connect('horseracing_hk')->name('horse_ranklist')->where("id = $courseId")->select();
		$key = str_replace('-' , '' , $res[0]['time']).$raceNumber;
		// var_dump($key);
		// die;
		$where = [
			'ranklistFK' => $courseId,
			'key'    => $key,
		];
		$result = Db::name('rank')->where($where)->select();
		$courseName = substr($result[0]['number'] , (strpos($result[0]['number'] , '-')+3));
		$t = preg_replace('/\D/s','',$result[0]['date']);
        $one = substr($t , 0 , 4);
        $two = substr($t , 4 , 2);
        $three = substr($t , 6 , 2);
        $time = $one.'-'.$two.'-'.$three.' '.$result[0]['time'];
        
		$data = [
			'raceId'  => $result[0]['id'],
			'courseId'  => $courseId,
			'raceNumber'   => 1,
			'date'         => trim($result[0]['date']),
			'courseName'   => $result[0]['place'],
 			'time'         => $time,
 			'track'        => trim($result[0]['track']),
 			'country'      => '香港',
 			'raceTitle'    => $courseName,
 			'distance'     => $result[0]['distance'],
 			'map'          => $result[0]['map'],
 		];
 		
 		$keyFK = $result[0]['key'];
 		// $result1 = Db::name('rankparticipant')->where("keyFK = $keyFK")->select();
 		$result1 = Db::connect('horseracing_hk')->name('horse_rankparticipant')->where("keyFK = $keyFK")->select();
 		$numberOfRunners = count($result1);
 		$data['numberOfRunners'] = $numberOfRunners;
 		foreach ($result1 as $k => $val) {
 			$data1 = [
 				'number' => $val['number'],
 				'clother' => $val['clother'],
 				'horse_name' =>$val['horse_name'],
 				'weightCarried'  => $val['weight'],
 				'jockey'        => $val['jockey'],
 				'gear'          => $val['gear'],
 				'trainer'       => $val['trainer'],
 				'score'         => $val['score'],
 				'score_float'   =>$val['score_float'],
 				'equipment'     => $val['equipment'],	
 			];
 			$horse_name = $val['horse_name'];
 			// var_dump($horse_name);
 			// die;
 			// $result2 = Db::name('horseinfo')->where("horse_name = '$horse_name'")->select();
 			$result2 = Db::connect('horseracing_hk')->name('horse_horseinfo')->where("horse_name = '$horse_name'")->select();
 			// var_dump($result2);
 			// die;
 			$data1['horseAge'] = $result2[0]['age'];
 			$data1['horseGender'] = $result2[0]['sex'];
 			$data1['ownerFullName'] = $result2[0]['owner'];
 			$data1['father'] = $result2[0]['father'];
 			$data1['mother'] = $result2[0]['mother'];

 			array_push($arr, $data1);

 		}
 		// var_dump($arr);
 		$raceNumbers = count(Db::connect('horseracing_hk')->name('horse_rank')->where("ranklistFK = $courseId")->select());
 		$arr1['basic'] = $data;
 		$arr1['entries'] = $arr;
 		$arr1['raceNumbers'] = $raceNumbers;

 		$arr2['data'] = $arr1;
 		$arr2['status'] = '200';
 		return json_encode($arr2);
	}
}