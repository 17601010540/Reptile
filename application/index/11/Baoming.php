<?php
namespace app\index\controller;
use think\Db;

class Baoming 
{
	public function List(){
		header("Access-Control-Allow-Origin: *");
		// header("Access-Control-Allow-Origin: http://www.leidata.com");
		//查询报名表列表
		$result = Db::name('eventlist')->select();
		$arr = array();
		$arr1 = array();
		foreach ($result as $key => $value) {
			//截取场地名称
			$coursename = substr($value['event_name'] , (strpos($value['event_name'] , '-')+2));
			$data = [
				'ccode'       => "HK",
				'courseId'    => $value['id'],
				'coursename'  => $coursename,
				'ymd'         => $value['time'],
			];
			array_push($arr, $data);
		}
		$arr1['data']  = $arr;
		$arr1['status'] = '200';
		return json_encode($arr1);
		
	}

	public function content(){
		header("Access-Control-Allow-Origin: *");
		// header("Access-Control-Allow-Origin: http://www.leidata.com");
		$courseId = $_GET['courseId'];
		// $courseId = 4;
		$result = Db::name('event')->where("eventlistFK = $courseId")->select();
		$arr  = array();
		$arr1 = array();
		
		$arr3 = array();
		foreach ($result as $key => $value) {
			$arr2 = array();
			$data = [
				'raceId'          => $value['id'],
				'courseId'        => $courseId,
				//赛马日期
				'meetingDate'     => $value['date'],
				//赛马场地
				'courseName'      => trim($value['place']),
				//国家
				'country'         => '香港',
				//赛事名称
				'raceTitle'       => $value['name'],
				//赛道
				'track'           => $value['track'],
				//距离
				'distance'        => $value['distance'],
				//分组
				'raceNumber'      => $value['group'],
			];
			// var_dump($value);
			$arr1['basic'] = $data;
			$key = $value['key'];
			$result1 = Db::name('participant')->where("event_keyFK = $key and reserve = 1")->select();
			foreach ($result1 as $k => $val) {
				$horse_name = trim($val['horse_name']);
				$data1 = [
					'raceId'    => $val['id'],
					'horseName' =>$horse_name,
					'trainer'   => $val['trainer'],
					'weightCarried'  => $val['weight'],
					'score'          => $val['score'],
					'score_float'    => $val['score_float'],
				];
				// var_dump($data1);
				array_push($arr2, $data1);

			}
			$arr1['entries'] = $arr2;
			array_push($arr, $arr1);
		}
		$arr3['data'] = $arr;
		$arr3['status'] = '200';
		echo json_encode($arr3);
	}
}
