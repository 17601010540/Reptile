<?php
namespace app\index\controller;
use QL\QueryList;
use think\Db;
set_time_limit(0);
class Result{

	public function result(){

		//需要采集的目标页面
		$url = 'http://racing.hkjc.com/racing/Info/meeting/Results/chinese/';
		// $url = 'http://racing.hkjc.com/racing/Info/meeting/Results/chinese/Local/20180715/ST';
		
		$html = file_get_contents($url);

		$part = '/<select name="raceDateSelect" id="raceDateSelect">(.*?)<\/select>/is';

		preg_match_all($part, $html, $matches);
		// var_dump($matches[0][0]);

		$data = Querylist::html($matches[0][0])->rules(array(
				'raceDate' => array('select>option' , 'value'),
			))->query()->getData();
		// var_dump($data);

		$time = str_replace('-' , '' , date('Y-m-d' , time()));
		// $time = '20181003';
		$array1 = array();
		$array2 = array();
		foreach ($data as $key => $value) {
			// echo preg_replace('/\D/s','',$value);
			if(preg_replace('/\D/s','',$value['raceDate']) == $time){

				array_push($array1, $key);
				array_push($array1, $key+1);
			}elseif((preg_replace('/\D/s','',$value['raceDate']) < $time)&&(empty($array1))){
				$array2 = [
					'0' => 1,
				];
			}
		}
		// var_dump($array1);
		// var_dump($array2);
		$array3 = array();
		if($array1){
			foreach ($array1 as $key => $value) {
				$url1 = $url.$data[$value]['raceDate'];
				array_push($array3, $url1);
			}
		}
		if($array2){
			foreach ($array2 as $key => $value) {
				$url1 = $url.$data[$value]['raceDate'];
				array_push($array3, $url1);
			}
		}
		// var_dump($array3);

		foreach ($array3 as $key => $value) {
			$html2 = file_get_contents($value);

			$part1 = '/<div class="boldFont14 color_white trBgBlue">(.*?)<\/div>/is';

			preg_match_all($part1, $html2, $matches1);

			if(!empty($matches1[0])){

				$part2 = '/<div class="raceNum clearfix">(.*?)<\/div>/is';

				preg_match_all($part2, $html2, $matches2);
				// var_dump($matches2[0][0]);
				$num = count(Querylist::html($matches2[0][0])->rules(array(
						'td' => array('tr:eq(0)>td>a' , 'html'),
					))->query()->getData());
				// var_dump($num);
				$this->execute($value , $num);
				// var_dump($value);

			}

			
		}
		
	}

	
	public function execute($url2 , $num){
		
		
		for ($i=1 ; $i <= $num ; $i++ ){

			$url3 = $url2.'/'.$i;
			// var_dump($url3);

			$html3 = file_get_contents($url3);
			// var_dump($html3);

			//日期-场地
			$part3 = '/<td class="tdAlignL number13 color_black">(.*?)<\/td>/is';
				
			preg_match_all($part3, $html3, $matches3);
			// var_dump($matches3[0]);
			
			$head = $matches3[0][0];
			// var_dump($matches3[0][0]);
			$d = Querylist::html($head)->rules(array(
					'td' => array('td' , 'text'),
				))->query()->getData();
			// var_dump($d);
			

			$date = substr($d[0][ 'td'] , (strpos($d[0]['td'] , ':')+2));
			//场地
			$place = substr($date , 18);
			//日期
			$date = substr($date, 0 , 10);
			$ar = explode('/' , $date);
			
			$dat = $ar[1].'月'.$ar[0].'日'.' - '.$place;
			// var_dump($dat);
			$time = $ar[2].'-'.$ar[1].'-'.$ar[0];
			$ar = [
				'result_name' => $dat,
				'time'        => $time,
			];
			// var_dump($ar);
			$result = Db::name('resultlist')->where($ar)->select();
            if(!$result){
                $res = Db::name('resultlist')->insert($ar);
            }
			

			$part4 = '/<div class="boldFont14 color_white trBgBlue">(.*?)<\/div>/is';

			preg_match_all($part4, $html3, $matches4);
			// var_dump($matches4);
			
			//第几场
			$data1 = Querylist::html($matches4[0][0])->rules(array(
					'number' => array('div' , 'text'),
				))->query()->getData();

			$part5 = '/<div class="clearDivFloat paddingTop5">(.*?)<\/div>/is';
			preg_match_all($part5, $html3, $matches5);
			// var_dump($matches5);
			
			$data2 = QueryList::html($matches5[0][0])->rules(array(
					'class' => array('tr:eq(0)>td:eq(0)' , 'text' , '-span'),
					'distance-score_range' => array('tr:eq(0)>td:eq(0)>span' , 'text'),
					'land' => array('tr:eq(0)>td:eq(2)' , 'text'),
					'name' => array('tr:eq(1)>td:eq(0)' , 'text'),
					'track' => array('tr:eq(1)>td:eq(2)' , 'text'),
					'td' => array('tr:eq(2)' , 'text'),
				))->query()->getData();
			// var_dump($data2[0]['td']);
			// $cou = count(explode(')' , $data2[0]['td']))-1;
			// var_dump($cou);die;
			//班次
			$class = substr($data2[0]['class'] , 0 , strpos($data2[0]['class'] , ' '));
			// var_dump($class);
			
			//赛程
			$distance = substr($data2[0]['distance-score_range'] , 0 , strpos($data2[0]['distance-score_range'] , ' - '));
			// var_dump($distance);
			
			//评分范围
			$score_range = substr($data2[0]['distance-score_range'] , (strpos($data2[0]['distance-score_range'] , ' - ')+3));
			$score_range = substr($score_range , 1);
			$score_range = substr($score_range , 0 , strpos($score_range , ')'));
			// var_dump($score_range);
			// var_dump($matches5[0][0]);
			
			$data3 = Querylist::html($matches5[0][0])->rules(array(
						'td' => array('tr:eq(2)>td' , 'html'), 
				))->query()->getData();
			// var_dump($data3);
			// die;
			
			$money = substr($data3[0]['td'] , (strpos($data3[0]['td'] , '$ ')+1));
			// var_dump($money);
			$resultlistFK = Db::name('resultlist')->where($ar)->select();
			$data6 = [
				'resultlistFK' => $resultlistFK[0]['id'],
				'number' => $data1[0]['number'],
				'class'  => $class,
				'distance' => $distance,
				'score_range'  => $score_range,
				'land'  => $data2[0]['land'],
				'name'  => $data2[0]['name'],
				'track'  => $data2[0]['track'],
				'money'  => $money,
				'date' => $date,
				'place' => $place,
				'key'         => preg_replace('/\D/s','',$date).$i,
			];
			// var_dump($data6);
			
			$result1 = Db::name('result')->where($data6)->select();
				if(!$result1){
					$res1 = Db::name('result')->insert($data6);
				}
			
			//具体信息
			$part6 = '/<div class="clearDivFloat rowDiv15">(.*?)<\/div>/is';
			// $part6 = '/<table cellpadding="1" cellspacing="1" class="tableBorder trBgBlue tdAlignC number12 draggable" width="760px">(.*?)<\/table>/is';
			preg_match_all($part6, $html3, $matches6);
			unset($matches6[0][1]);
			$num3 = count(Querylist::html($matches6[0][0])->rules(array(
					'td' => array('tr:eq(1)>td' , 'html'),
				))->query()->getData());
			$num3 = $num3-1;
			$num4 = $num3-1;
			// var_dump($num3);
			// var_dump($matches6[0][0]);
			// die;
			
			$par = '/<tr class="(trBgWhite|trBgGrey)">(.*?)<\/div>/is';
			preg_match_all($par, $matches6[0][0], $matches6);
			// var_dump($matches6[0]);
			$ar2 = array();
			$ar = explode('<tr class="trBgGrey">' , $matches6[0][0]);
			foreach ($ar as $key => $value) {
				$ar1 = explode('<tr class="trBgWhite">' , $value);
				foreach ($ar1 as $k => $val) {
					array_push($ar2, $val);
				}
			}
			
			unset($ar2[0]);
			// var_dump($ar2);
			// $ar = explode('<tr class="trBgWhite">' , $ar);
			// var_dump($ar);
			// die;
			$arr2 = array();
			foreach ($ar2 as $k => $val) {
				$ar2 = explode('</td>' , $val);
				// unset($ar2);
				$cou = count($ar2);
				// var_dump($ar2);
				// var_dump($cou);

				$finish_time = array_slice($ar2 , -3 , 1);
				$finish_time = strip_tags($finish_time[0]);
				$odds = array_slice($ar2 , -2 , 1);
				$odds = strip_tags($odds[0]);
				if($cou <= 12){
					$data7 = [
						'ranking' => strip_tags($ar2[0]),
						'number'  => '',
						'horse_name'  => strip_tags($ar2[1]),
						'jockey'   => strip_tags($ar2[2]),
						'trainer'   => strip_tags($ar2[3]),
						'weight'   => strip_tags($ar2[4]),
						'displacement_weight'   => strip_tags($ar2[5]),
						'gear'  => strip_tags($ar2[6]),
						'headHorse_distance' => strip_tags($ar2[7]),
						'finish_time' =>$finish_time,
						'odds'      => $odds,
						'keyFK'     => preg_replace('/\D/s','',$date).$i,
					];
				}else{
					$data7 = [
						'ranking' => strip_tags($ar2[0]),
						'number'  => strip_tags($ar2[1]),
						'horse_name'  => strip_tags($ar2[2]),
						'jockey'   => strip_tags($ar2[3]),
						'trainer'   => strip_tags($ar2[4]),
						'weight'   => strip_tags($ar2[5]),
						'displacement_weight'   => strip_tags($ar2[6]),
						'gear'  => strip_tags($ar2[7]),
						'headHorse_distance' => strip_tags($ar2[8]),
						'finish_time' =>$finish_time,
						'odds'      => $odds,
						'keyFK'     => preg_replace('/\D/s','',$date).$i,
					];
				
				}			
				
				array_push($arr2, $data7);
			
			}
			// var_dump($arr2);
				// die;
			foreach ($arr2 as $key => $value) {
				// var_dump($value);
				$result1 = Db::name('resultinfo')->where($value)->select();
					
					if(!$result1){
						$res1 = Db::name('resultinfo')->insert($value);
					}
				// die;
			}
			
			//派彩
				$part7 = '/<div class="rowDivLeft rowDiv10">(.*?)<\/div>/is';
				preg_match_all($part7, $html3, $matches7);
				
				$part8 = '/<tr class="(trBgWhite|trBgGrey1)">(.*?)<\/tr>/is';
				
				preg_match_all($part8, $matches7[0][0], $matches8);
				$td_part = '/<td (rowspan="(.*?)"|class="number14"|class="number14 tdAlignR"|class="tdAlignR")>(.*?)<\/td>/is';

				foreach ($matches8[0] as $k => $v) {
					$arr1 = array();
					preg_match_all($td_part, $v, $td);
					// var_dump($td[0]);
					$td_num = count($td[0]);
					// echo $td_num;
					// echo '<br />';
					if($td_num == 3){
						$Lottery_pool = strip_tags($td[0][0]);
						$Win_combination = strip_tags($td[0][1]);
						$payout = strip_tags($td[0][2]);
						$arr1['Lottery_pool'] = $Lottery_pool;
						$arr1['Win_combination'] = $Win_combination;
						$arr1['payout'] = $payout;
						$arr1['keyFK'] = preg_replace('/\D/s','',$date).$i;
					}
					//暂时存放彩池
					$tem = $Lottery_pool;
					if($td_num == 2){
						$Lottery_pool = $tem;
						$Win_combination = strip_tags($td[0][0]);
						$payout = strip_tags($td[0][1]);
						$arr1['Lottery_pool'] = $Lottery_pool;
						$arr1['Win_combination'] = $Win_combination;
						$arr1['payout'] = $payout;
						$arr1['keyFK'] = preg_replace('/\D/s','',$date).$i;
					}
					if($td_num == 1){
						$Lottery_pool = $tem;
						$payout = strip_tags($td[0][0]);
						$arr1['Lottery_pool'] = $Lottery_pool;
						$arr1['payout'] = $payout;
						$arr1['keyFK'] = preg_replace('/\D/s','',$date).$i;
					}
					
					//存入数据库
					$result1 = Db::name('payout')->where($arr1)->select();
					if(!$result1){
						$res1 = Db::name('payout')->insert($arr1);
					}
		
					
				}
				
			}
			
		
		}
		
	
	 	
}