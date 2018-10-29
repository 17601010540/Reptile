<?php

namespace app\index\controller;
use QL\QueryList;
use think\Db;
include 'Trans.php';

class registration{

	public function registration()
	{
		set_time_limit(0);
		ini_set('memory_limit', '1000M');
		$tableDir = '';
		$utf8_st_class=new Trans($tableDir);
		// die;
		//需要采集的目标页面
		$url = 'http://racing.hkjc.com/racing/info/meeting/RaceCard/Chinese/Local';
		//采集html
		$html = file_get_contents($url);

	    $p = '/<a class="blueBtn">(.*?)<\/a>/is';

        preg_match_all($p, $html, $match);

        //赛事
        
        $d = Querylist::html($match[0][0])->rules(array(
                'rank_name' => array('a' , 'text'),
            ))->query()->getData();
        $d = $d[0];
       	
        $part = '/<div class="raceNum clearfix">(.*?)<\/div>/is';

        preg_match_all($part, $html, $matches);
        // var_dump($matches[0][0]);
        // die;
		//场数
		$num = count(Querylist::html($matches[0][0])->rules(array(
				'td' => array('td' , 'html'),
			))->query()->getData())-3;
		// var_dump($num);
		//拼接url地址
		$u = Querylist::html($matches[0][0])->rules(array(
				'a' => array('td:eq(3)>a' , 'href'),
			))->query()->getData();
		$ur = substr($u[0]['a'] , 0 , strrpos($u[0]['a'], '/'));
		$ur = substr($ur , 43);
		$t = preg_replace('/\D/s','',$ur);
        $one = substr($t , 0 , 4);
        $two = substr($t , 4 , 2);
        $three = substr($t , 6 , 2);
        $tim = $one.'-'.$two.'-'.$three;
        if(strpos($d['rank_name'] , '/')){
        	$d['rank_name'] = substr($d['rank_name'] , 0 , (strpos($d['rank_name'] , '/')-1));
        }else{
        	$d['rank_name'] = $d['rank_name'];
        }
        $d['rank_name'] = $utf8_st_class-> utf8_t2s($d['rank_name']);
        $d['time'] = $utf8_st_class-> utf8_t2s($tim);
        $result = Db::name('ranklist')->where($d)->find();
        if(!$result){
            $res = Db::name('ranklist')->insert($d);
        }else{
        	$id = $result['id'];
        	$res = Db::name('ranklist')->where("id = $id")->update($d);
        }
		$ur = $url.$ur;
		// echo $ur;
		// die;
		for ($i=1 ; $i <= $num ; $i++ ) { 
			$url1 = $ur.'/'.$i;
			// echo $url1."\n";
			// echo $url1;
			// echo '<br />';
			$html1 = file_get_contents($url1);

			$part1 = '/<div class="rowDiv10">(.*?)<\/div>/is'; 

			preg_match_all($part1, $html1, $matches1);
			
			$value = $matches1[0][0];
			// var_dump($value);
			// die;
			$arr3 = array();
			$arr4 = array();
			$arr5 = array();
			
				$data = Querylist::html($value)->rules(array(
					'span' => array('.bold' , 'text'),
					'td'   => array('td' , 'text' , '-span'),
				))->query()->getData();	
				// var_dump($data[0]);

				$arr = explode(', ' , $data[0]['td']);
				//日期
				$arr1 = explode(',' , $arr[0]);
				// var_dump($arr1);
				// die;
				$date = $arr1[0];
				// var_dump($date);
				//星期
				$week = $arr1[1];
				// var_dump($week);
				//场地
				$place = $arr[1];
				
				//具体时间,几点几分
				$time = substr($arr[2] , 0 , 5);
				//跑道
				$track = str_replace(' ' , '' , substr($arr[2] , 5));
				// var_dump($track);
				// die;
				// var_dump($arr);
				
				if(count($arr) == 7){
					//赛道
					$runway = ''; 
					//评分范围
					$score_range = substr($arr[5], 7);
					//班
					$class = $arr[6];
					//distance
					$distance = substr($arr[4] , 0 , (strpos($arr[4] , '米')+3));
					// var_dump($distance);
					//地
					if((strpos($arr[4] , '米')+3) == (strpos($arr[4] , '獎'))){
						$land = '';
					}else{
						$land = substr($arr[4] ,  (strpos($arr[4] , '米')) , (strpos($arr[4] , '獎')));
					}
					
					// var_dump($land);
					
				}elseif(count($arr) == 8){
					$runway = $arr[3];
					$score_range = substr($arr[6], 7);
					$class = $arr[7];
					$distance = substr($arr[4] , 0 , (strpos($arr[4] , '米')+3));
					// var_dump($distance);
					//地
					if((strpos($arr[5] , '米')+3) == (strpos($arr[5] , '獎'))){
						$land = '';
					}else{
						$land = substr($arr[5] ,  (strpos($arr[5] , '米')) , (strpos($arr[5] , '獎')));
					}
				}
				// die;
				//奖金
				if(stripos($arr[4] , '$')){
					$money = substr(strstr($arr[4] , '$') , 1);
				}else if(stripos($arr[5], '$')){
					$money = substr(strstr($arr[5] , '$') , 1);
				}
				//地图
				$par = '/<div class="rowDivRight divWidth360">(.*?)<div class="rowDiv5">/is';

				preg_match_all($par, $html1 , $mat);

				$ar = explode('<div class="rowDivRight">' , $mat[0][0]);

				preg_match_all('/<img[^>]*?src="([^"]*?)"[^>]*?>/i',$ar[2],$mat1);
				// var_dump($mat1[1]);

				$map = substr($mat1[1][0] , (strripos($mat1[1][0] , '/')+1));
				
				$ranklistFK = Db::name('ranklist')->where($d)->select();
				
				$arr5 = [
					'ranklistFK'    => $ranklistFK[0]['id'],
					'number'      => $utf8_st_class-> utf8_t2s($data[0]['span']),
					'date'        => $utf8_st_class-> utf8_t2s($date),
					'week'        => $utf8_st_class-> utf8_t2s($week),
					'place'       => $utf8_st_class-> utf8_t2s($place),
					'time'        => $utf8_st_class-> utf8_t2s($time),
					'track'       => $utf8_st_class-> utf8_t2s($track),
					// 'runway'      => $utf8_st_class-> utf8_t2s($runway),
					'distance'    => $utf8_st_class-> utf8_t2s($distance),
					'money'       => $utf8_st_class-> utf8_t2s($money),
					'score_range' => $utf8_st_class-> utf8_t2s($score_range),
					'class'       => $utf8_st_class-> utf8_t2s($class),
					'key'         => $utf8_st_class-> utf8_t2s(preg_replace('/\D/s','',$date).$i),
					// 'land'        => $utf8_st_class-> utf8_t2s($land),
					'map'         => $utf8_st_class-> utf8_t2s($map),
				];
				// var_dump($arr5);
				// die;
				// $ranklistFK = $arr5['ranklistFK'];
				// $number = $arr5['number'];
				$rankwhere = [
					'ranklistFK' => $arr5['ranklistFK'],
					'number'     => $arr5['number'],
				];
				$result = Db::name('rank')->where($rankwhere)->find();
				if(!$result){
					$res1 = Db::name('rank')->insert($arr5);
				}else{
					$id = $result['id'];
					$res1 = Db::name('rank')->where("id = $id")->update($arr5);
				}

				//排位表

				$part2 = '/<tr class="(font13 tdAlignC trBgWhite|font13 tdAlignC trBgGrey1)">(.*?)<\/tr>/is';
				preg_match_all($part2, $html1, $matches2);
				// print_r($matches2);
				// var_dump($matches2[0]);
				foreach ($matches2[0] as $ke => $val) {
					$data1 = QueryList::html($val)->rules(array(
							'number' => array('td:eq(0)' , 'text'),
							'record' => array('td:eq(1)' , 'text'),
							'clother'=> array('td:eq(2)>img' , 'src'),
							'horse_name' => array('td:eq(3)' , 'text'),
							'horse_name_url' => array('td:eq(3)>a' , 'href'),
							'weight'     => array('td:eq(5)' , 'text'),
							'jockey'     => array('td:eq(6)' , 'text'),
							'gear'       => array('td:eq(8)' , 'text'),
							'trainer'    => array('td:eq(9)' , 'text'),
							'score'      => array('td:eq(10)' , 'text'),
							'score_float'=> array('td:eq(11)' , 'text'),
							'displacement_weight' => array('td:eq(12)' , 'text'),
							'execllent'  => array('td:eq(19)' , 'text'),
							'equipment'  => array('td:eq(20)' , 'text'),
						))->query()->getData();
					$arr4 = $data1[0];
					$arr4['horse_name'] = $utf8_st_class-> utf8_t2s($arr4['horse_name']);
					$arr4['trainer'] = $utf8_st_class-> utf8_t2s($arr4['trainer']);
					// var_dump($arr4);
					//马的地址
					$start = strpos($arr4['horse_name_url'] , '?')+1;
					$arr4['horse_name_url'] = substr($arr4['horse_name_url'] , $start);
					// var_dump($arr4['horse_name_url']);
					$final = strpos($arr4['horse_name_url'] , '\',');
					$horse_name_url = substr($arr4['horse_name_url'] , 0 , $final);
					// var_dump($horse_name_url);
					$horse_name_url = str_replace('h', 'H', $horse_name_url);
					$horse_name_url = str_replace('n', 'N', $horse_name_url);
					$horse_name_url = 'http://racing.hkjc.com/racing/information/Chinese/Horse/Horse.aspx?'.$horse_name_url;
					var_dump($horse_name_url);
					$htm1 = file_get_contents($horse_name_url);
					$par1 = '/<table border="0" width="492" class="table_top_right">(.*?)<\/table>/is';

					preg_match_all($par1, $htm1, $match1);

					// var_dump($match1[0]);
					$match1 = $match1[0][0];
					$dat = Querylist::html($match1)->rules(array(
							'age' => array('tr:eq(0)>td:eq(1)' , 'text'),
							'sex' => array('tr:eq(1)>td:eq(1)' , 'text'),
							'owner' => array('tr:eq(1)>td:eq(3)' , 'text'),
							'father' => array('tr:eq(4)>td:eq(3)' , 'text'),
							'mother' => array('tr:eq(5)>td:eq(3)' , 'text'),
						))->query()->getData();

					// var_dump($dat);

					$age = substr($dat[0]['age'] , (strpos($dat[0]['age'] , '/')+2));
					$sex = substr($dat[0]['sex'] , (strpos($dat[0]['sex'] , '/')+2));
					$owner = substr($dat[0]['owner'] , 2);
					$father = substr(trim($dat[0]['father']) , 82);
					$mother = substr(trim($dat[0]['mother']) , 2);
					$ar1 = array();
					$ar1 = [
						'horse_name' => $utf8_st_class-> utf8_t2s($arr4['horse_name']),
						'age'        => $utf8_st_class-> utf8_t2s($age),
						'sex'        => $utf8_st_class-> utf8_t2s($sex),
						'owner'      => $utf8_st_class-> utf8_t2s($owner),
						'father'     => $utf8_st_class-> utf8_t2s($father),
						'mother'     => $utf8_st_class-> utf8_t2s($mother),

					];
					// var_dump($ar1);

					$horseinfowhere = [
						'horse_name' => $ar1['horse_name'],
						'age'     => $ar1['age'],
					];
					$result2 = Db::name('horseinfo')->where($horseinfowhere)->find();
					if(!$result2){
						$res1 = Db::name('horseinfo')->insert($ar1);
					}else{
						$id = $result2['id'];
						$res1 = Db::name('horseinfo')->where("id = $id")->update($ar1);
					}
					//彩衣
					$arr4['clother'] = substr($arr4['clother'] , (strripos($arr4['clother'] , '/')+1));
					// die;
					$arr4['keyFK'] = preg_replace('/\D/s','',$date).$i;
					if(strpos($arr4['jockey'] ,'(')){
						//骑师
						$jockey = substr($arr4['jockey'] , 0 ,(strpos($arr4['jockey'] ,'(')));
					}else{
						$jockey = $arr4['jockey'];
					}	
					//让磅
					$let_pound = preg_replace('/\D/s','',$arr4['jockey']);
					//优
					$execllent = preg_replace('/\D/s','',$arr4['execllent']);
					$remark   = substr($arr4['execllent'] , 0 , 1);
					// var_dump($let_pound);
					$arr4['jockey'] = $utf8_st_class-> utf8_t2s($jockey);
					$arr4['let_pound'] = $let_pound;
					$arr4['execllent'] = $execllent;
					$arr4['remark']    = $remark;
					unset($arr4['horse_name_url']);
					// var_dump($arr4);

					// die;
					$rankparticipantwhere = [
						'keyFK' => $arr4['keyFK'],
						'number'     => $arr4['number'],
					];
					$result1 = Db::name('rankparticipant')->where($rankparticipantwhere)->find();
					if(!$result1){
						$res1 = Db::name('rankparticipant')->insert($arr4);
					}else{
						$id = $result1['id'];
						$res1 = Db::name('rankparticipant')->where("id = $id")->update($arr4);
					}
					// die;
				}
				
				//后备马匹
				$part3 = '/<tr class="trBgWhite tdAlignV tdAlignC font13">(.*?)<\/tr>/is';
				preg_match_all($part3, $html1, $matches3);
				// print_r($matches3[0]);
				foreach ($matches3[0] as $k => $v) {
					$data2 = Querylist::html($v)->rules(array(
							'number' => array('td:eq(0)' , 'text'),
							'horse_name' => array('td:eq(1)' , 'text'),
							'displacement_weight' => array('td:eq(2)' , 'text'),
							'weight'   => array('td:eq(3)' , 'text'),
							'score'    => array('td:eq(4)' , 'text'),
							'horse_age' => array('td:eq(5)' , 'text'),
							'record'    => array('td:eq(6)' , 'text'),
							'jockey'    => array('td:eq(7)' , 'text'),
							'execllent' => array('td:eq(8)' , 'text'),
							'equipment' => array('td:eq(9)' , 'text'),
						))->query()->getData();
					
					$arr3 = $data2[0];
					$arr3['horse_name'] = $utf8_st_class-> utf8_t2s($arr3['horse_name']);
					$arr3['jockey'] = $utf8_st_class-> utf8_t2s($arr3['jockey']);
					$arr3['keyFK'] = preg_replace('/\D/s','',$date).$i;
					// var_dump($arr3);
					// die;
					$rankreservewhere = [
						'keyFK' => $arr3['keyFK'],
						'number'     => $arr3['number'],
					];
					$result2 = Db::name('rankreserve')->where($rankreservewhere)->find();
					if(!$result2){
						$res1 = Db::name('rankreserve')->insert($arr3);
					}else{
						$id = $result2['id'];
						$res1 = Db::name('rankreserve')->where("id = $id")->update($arr3);
					}
				}

			
		}
		
	}
}


















