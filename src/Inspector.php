<?php

namespace think\Inspector;

use Inspector\Inspector as InspectorOrignal;
use Inspector\Configuration;
use think\facade\Config;

class Inspector
{
    public $ins;

    public $options = [
        'key'       => '',
        'enable'    => true,
        'max_items' => 100,
        'env'       => 'test',
        'single'    => true,
    ];

    public $segments = [];

    public $hasTrans = false;

    public function __construct()
    {
        $this->options = array_merge($this->options, Config::get('inspector'));
        $configuration = new Configuration($this->options['key']);
        $configuration->setEnabled($this->options['enable']);
        $configuration->setMaxItems($this->options['max_items']);
        $ins           = new InspectorOrignal($configuration);
        $this->ins     = $ins;

    }

    public function __call($name, $arguments)
    {
        return call_user_func_array([$this->ins, $name], $arguments);
    }

    public static function getPath($single = true){
        $app = app();
        if($app->runningInConsole()){
            if($single == false){
                return '命令行';
            }else{
                $argv = $app->request->server('argv');
                if(empty($argv)){
                    return '';
                }else{
                    array_shift($argv);
                    return implode(' ', $argv);
                }
            }
        }else{
            if($single == false){
                return 'WEB';
            }else{
                $_SERVER['REQUEST_URI'] = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI']: '';
                $pathInfo = explode('?', $_SERVER['REQUEST_URI']);
                $path     = array_shift($pathInfo);
                return $path;
            }
        }
    }

    public static function summaryInfo(){
        $app  = app();
        $runtime    = round(microtime(true) - $app->getBeginTime(), 10);
        $reqs       = $runtime > 0 ? number_format(1 / $runtime, 2) : '∞';
        $time_str   = ' [运行时间：' . number_format($runtime, 6) . 's][吞吐率：' . $reqs . 'req/s]';
        $memory_use = number_format((memory_get_usage() - $app->getBeginMem()) / 1024, 2);
        $memory_str = ' [内存消耗：' . $memory_use . 'kb]';
        $file_load  = ' [文件加载：' . count(get_included_files()) . ']';

        if (isset($_SERVER['HTTP_HOST'])) {
            $current_uri = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        } else {
            $current_uri = 'cmd:' . implode(' ', $_SERVER['argv']);
        }
        if($app->runningInConsole()){
            $str = "{$current_uri}{$time_str}{$memory_str}{$file_load}";
        }else{
            $str = "应用开始 {$current_uri}{$time_str}{$memory_str}{$file_load}";
        }
        return $str;
    }

    public function startSegment($type, $label = ''){
        if($this->ins->canAddSegments()){
            $key                   = $this->ins->startSegment($type, $label);
            $this->segments[$type] = $key;
            return $key;
        }else{
            return null;
        }
    }

    public function end($type, $duration = null){
        if(isset($this->segments[$type])){
            $ret = $this->segments[$type]->end($duration);
            unset($this->segments[$type]);
            return $ret;
        }
    }
}