<?php
/**
 * Created by PhpStorm.
 * User: DFFuture
 * Date: 2017/5/23
 * Time: 22:22
 */

namespace app\index\Parser;

// 工具经历模块解析方法
class BlockCareer extends AbstractParser {
    protected $patterns = array(
        1=> '/(.+) (\d{4}\D+\d{1,2})\D+(\d{4}\D+\d{1,2}|至今|现在)$/',
    );

    /**
     * @param array $data      区块dom数组
     * @param string $methods  提取方案序号
     * @return array
     */
    public function parse($data, $methods = '') {
        $jobs = array();
        if($methods && is_string($methods)){
            $methods = explode(',', $methods);
        }
        foreach($methods as $method) {
            if(preg_match($this->patterns[$method], $data[0])) {
                $method = 'extract'.$method;
                //dump($method);
                $jobs = $this->$method($data);
                break;
            }
        }
        return $jobs;
    }

    public function extract1($data) {
        $length = count($data);
        $i = 0;
        $j = 0;
        $currentKey = '';
        $jobs = array();
        $job = array();
        $rules = array(
            array('city', '-所在地区：', 0),
            array('report_to', '-汇报对象：', 0),
            array('underlings', '-下属人数：', 0),
            array('duty', '-工作职责：|主要工作:'),
            array('performance', '-工作业绩：'),
        );
        while($i < $length) {
            //正则匹配
            if(preg_match('/(.+) (?P<start_time>\d{4}\D+\d{1,2})\D+(?P<end_time>\d{4}\D+\d{1,2}|至今|现在)$/',
                $data[$i], $match)) {

                $job = array();
                $job['company'] = $match[1];
                $job['start_time'] = Utility::str2time($match["start_time"]);
                $job['end_time'] = Utility::str2time($match['end_time']);
            }elseif(preg_match('/^(?P<start_time>\d{4}\D+\d{1,2})\D+(?P<end_time>\d{4}\D+\d{1,2}|至今|现在)$/',
                $data[$i], $match)) {

                $jobs[$j++] = $job;
                $jobs[$j-1]['position'] = $data[$i-1];
                $jobs[$j-1]['start_time'] = Utility::str2time($match["start_time"]);
                $jobs[$j-1]['end_time'] = Utility::str2time($match['end_time']);
            }elseif($KV = $this->parseElement($data, $i, $rules)) {
                $jobs[$j-1][$KV[0]] = $KV[1];
                $i = $i + $KV[2];
                $currentKey = $KV[0];
            }elseif($currentKey){
                $jobs[$j-1][$currentKey] .=  $data[$i];
            }
            $i++;
        }
        return $jobs;
    }
}
