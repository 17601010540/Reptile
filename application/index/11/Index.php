<?php
namespace app\index\controller;
//引入自动加载文件
// require 'vendor/autoload.php';
use QL\QueryList;
use think\Db;
include 'Trans.php';
ignore_user_abort(); // 后台运行
class Index
{
    public function index()
    {
        $tableDir = '';
        $utf8_st_class=new Trans($tableDir);

        $url = 'http://racing.hkjc.com/racing/Info/meeting/Entries/chinese/Local/';

        $par = '/<div class="rowDiv15">(.*?)<\/div>/is'; 

        //采集html
        $html1 = file_get_contents($url);

        preg_match_all($par, $html1, $mat);
        // var_dump($mat[0]);
        $ur = Querylist::html($mat[0][0])->rules(array(
                'a' => array('a' , 'href'),
            ))->query()->getData();
        $ur = substr($ur[0]['a'] , 63 , 15);
        
        $url = $url.$ur;

        $html1 = file_get_contents($url);

        $p = '/<a class="blueBtn">(.*?)<\/a>/is';

        preg_match_all($p, $html1, $match);
        // var_dump($match);
        //赛事

        $t = preg_replace('/\D/s','',$ur);
        $one = substr($t , 0 , 4);
        $two = substr($t , 4 , 2);
        $three = substr($t , 6 , 2);
        $tim = $one.'-'.$two.'-'.$three;

        $d = Querylist::html($match[0][0])->rules(array(
                'event_name' => array('a' , 'text'),
            ))->query()->getData();
        $d = $d[0];
        $d['time'] = $tim;
        $result = Db::name('eventlist')->where($d)->select();
            if(!$result){
                $res = Db::name('eventlist')->insert($d);
            }
        // var_dump($d);
        // die;
        
        $part = '/<div class="rowDiv5">(.*?)<div class="rowDiv15">/is';

        preg_match_all($part, $html1, $matches);
        // var_dump($matches[0]);
        // print_r($matches[0]);
        
        $arr1 = array();
        $arr2 = array();
        $arr3 = array();
        foreach ($matches[0] as $key => $html) {
            $data1 = Querylist::html($html)->rules(array(
                'time'              => array('.ulDiv:eq(0)>li>.number13:eq(0)' , 'text'),
                'place'             => array('.ulDiv:eq(0)>li:eq(0)' , 'text' , '-span'),
                'track'             => array('.ulDiv:eq(0)>li:eq(1)' , 'text'),
                'track_value'       => array('.ulDiv>li:eq(2)' , 'text'),
                'date'              => array('.ulDiv:eq(1)>li:eq(0)' , 'text'),
                'date_value'        => array('.ulDiv:eq(1)>li:eq(1)' , 'text'),
                'runway'            => array('.ulDiv:eq(1)>li:eq(2)' , 'text'),
                'runway_value'      => array('.ulDiv:eq(1)>li:eq(3)' , 'text'),
                'name'              => array('.ulDiv:eq(2)>li:eq(0)' , 'text'),
                'name_value'        => array('.ulDiv:eq(2)>li:eq(1)' , 'text'),
                'distance'          => array('.ulDiv:eq(2)>li:eq(2)' , 'text'),
                'distance_value'    => array('.ulDiv:eq(2)>li:eq(3)' , 'text'),
                'class'             => array('.ulDiv:eq(3)>li:eq(0)' , 'text'),
                'class_value'       => array('.ulDiv:eq(3)>li:eq(1)' , 'text'),
                'group'             => array('.ulDiv:eq(3)>li:eq(2)' , 'text'),
                'group_value'       => array('.ulDiv:eq(3)>li:eq(3)' , 'text'),
                'score_range'       => array('.ulDiv:eq(4)>li:eq(0)' , 'text'),
                'score_range_value' => array('.ulDiv:eq(4)>li:eq(1)' , 'text'),
            ))->range('.rowDiv5')->query()->getData();
            // var_dump($data1[0]['time']);
            array_push($arr1, $data1[0]);
            // var_dump($arr1);

            //正式
            $part = '/<div class="rowDiv10">(.*?)<\/div>/is';
            preg_match_all($part, $html, $html2);
            // print_r($html2[0]);
            // die;
            foreach ($html2[0] as $key => $value) {
                $data3 = Querylist::html($value)->rules(array(
                    'horse_name_value' => array('td:eq(0)' , 'text'),
                    'trainer_value'    => array('td:eq(1)' , 'text'),
                    'weight_value'     => array('td:eq(2)' , 'text'),
                    'pound_value'      => array('td:eq(3)' , 'text'),
                    'score_value'      => array('td:eq(4)' , 'text'),
                    'score+/-_value'   => array('td:eq(5)' , 'text'),
                    'execllent_value'  => array('td:eq(6)' , 'text'),
                    'remark_value'     => array('td:eq(7)' , 'text'),
                ))->range('.rowDiv10 tr')->query()->getData();
                // var_dump($data3);
                unset($data3[0]);
                $data3['time'] = $data1[0]['time'];
                array_push($arr2, $data3);
                // var_dump($arr2);
            }

            //后备
            if(strpos($html,'後備')){
                $part2 = '/<div class="rowDiv15 font13">後備 :<\/div>(.*?)<\/table><\/div>/is';
                preg_match_all($part2, $html, $html3);
                // print_r($html3[0]);
                foreach ($html3[0] as $k => $val) {
                    $data4 = Querylist::html($val)->rules(array(
                        'number_value'     => array('td:eq(0)' , 'text'),
                        'horse_name_value' => array('td:eq(1)' , 'text'),
                        'trainer_value'    => array('td:eq(2)' , 'text'),
                        'weight_value'     => array('td:eq(3)' , 'text'),
                        'pound_value'      => array('td:eq(4)' , 'text'),
                        'score_value'      => array('td:eq(5)' , 'text'),
                        'score+/-_value'   => array('td:eq(6)' , 'text'),
                        'execllent_value'  => array('td:eq(7)' , 'text'),
                        'remark_value'     => array('td:eq(8)' , 'text'),
                        ))->range('.rowDiv5 tr')->query()->getData();
                    unset($data4[0]);
                    // var_dump($data4);
                    $data4['time'] = $data1[0]['time'];
                    array_push($arr3, $data4);
                    // var_dump($arr3);
                }
            }
        }

        // var_dump($arr1);
        // var_dump($arr2);
        // var_dump($arr3);
       
        
        $eventlistFK = Db::name('eventlist')->where($d)->select();
        // var_dump($eventlistFK[0]['id']);

        // die;
        $data  = array();
        $data1 = array();
        $data2 = array();
        
        //赛程表
        foreach ($arr1 as $key => $value) {
            
            $key = str_replace("/" , '' , $value['time']).$key;

            $data = [
                'eventlistFK'  => $utf8_st_class-> utf8_t2s($eventlistFK[0]['id']),
                'key'          => $utf8_st_class-> utf8_t2s($key),
                'date'         => $utf8_st_class-> utf8_t2s($value['time']),
                'place'        => $utf8_st_class-> utf8_t2s(trim($value['place'])),
                'track'        => $utf8_st_class-> utf8_t2s(trim($value['track_value'])),
                'long'         => $utf8_st_class-> utf8_t2s((int)$value['date_value']),
                'runway'       => $utf8_st_class-> utf8_t2s($value['runway_value']),
                'name'         => $utf8_st_class-> utf8_t2s(trim($value['name_value'])),
                'distance'     => $utf8_st_class-> utf8_t2s(trim($value['distance_value'])),
                'class'        => $utf8_st_class-> utf8_t2s($value['class_value']),
                'group'        => $utf8_st_class-> utf8_t2s((int)$value['group_value']),
                'score_range'  => $utf8_st_class-> utf8_t2s($value['score_range_value']),
            ];
            $result = Db::name('event')->where($data)->select();
            if(!$result){
                $res = Db::name('event')->insert($data);
            }
            
        }
        

        
        //参赛记录表
        foreach ($arr2 as $key => $value) {
            // var_dump($value);
            $eventFK = (str_replace("/" , '' , $value['time'])).$key;
            // var_dump($eventFK);
            
                foreach ($value as $k => $val) {
                    
                    if(is_array($val)){
                        $data1 = [
                            'event_keyFK'   => $utf8_st_class-> utf8_t2s($eventFK),
                            'horse_name'    => $utf8_st_class-> utf8_t2s($val['horse_name_value']),
                            'trainer'       => $utf8_st_class-> utf8_t2s($val['trainer_value']),
                            'weight'        => $utf8_st_class-> utf8_t2s($val['weight_value']),
                            'pound'         => $utf8_st_class-> utf8_t2s($val['pound_value']),
                            'score'         => $utf8_st_class-> utf8_t2s($val['score_value']),
                            'score_float'   => $utf8_st_class-> utf8_t2s($val['score+/-_value']),
                            'execllent'     => $utf8_st_class-> utf8_t2s($val['execllent_value']),
                            'remark'        => $utf8_st_class-> utf8_t2s($val['remark_value']),
                            'reserve'       => 1,
                        ];  
                    }
                // var_dump($data1);
                $result1 = Db::name('participant')->where($data1)->select();
                // var_dump($result);
                if(!$result1){
                    $res1 = Db::name('participant')->insert($data1);
                }
                
            }
                
            // }
        }
        

        
        //后备
        foreach ($arr3 as $key => $value) {
            $eventFK = (str_replace("/" , '' , $value['time'])).$key;
            
                foreach ($value as $k => $val) {
                // var_dump($val);
                    if(is_array($val)){
                        $data2 = [
                            'event_keyFK'   => $utf8_st_class-> utf8_t2s($eventFK),
                            'horse_name'    => $utf8_st_class-> utf8_t2s($val['horse_name_value']),
                            'trainer'       => $utf8_st_class-> utf8_t2s($val['trainer_value']),
                            'weight'        => $utf8_st_class-> utf8_t2s($val['weight_value']),
                            'pound'         => $utf8_st_class-> utf8_t2s($val['pound_value']),
                            'score'         => $utf8_st_class-> utf8_t2s($val['score_value']),
                            'score_float'   => $utf8_st_class-> utf8_t2s($val['score+/-_value']),
                            'execllent'     => $utf8_st_class-> utf8_t2s($val['execllent_value']),
                            'remark'        => $utf8_st_class-> utf8_t2s($val['remark_value']),
                            'reserve'       => 2,
                            'number'        => $utf8_st_class-> utf8_t2s($val['number_value']),
                        ];
                    }
                // var_dump($data2);
                $result2 = Db::name('participant')->where($data2)->select();
                if(!$result2){
                    $res1 = Db::name('participant')->insert($data2);
                }
                
            }

        }
    }   

}
 
