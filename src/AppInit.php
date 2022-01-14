<?php
declare (strict_types = 1);

namespace think\Inspector;

use think\facade\Config;

class AppInit
{
    /**
     * 事件监听处理
     *
     * @return mixed
     */
    public function handle($event)
    {
        $config = Config::get('inspector');
        if($config['enable']){
            $this->app  = app();
            if(!$this->app->runningInConsole()){
                if(!isset($this->app->inspector)){
                    $this->app->bind('inspector', Inspector::class);
                }
                $ins = $this->app->inspector;
                if($ins->needTransaction()){
                    $path = Inspector::getPath($config['single']);
                    // trace('init path '. $path);
                    $trans = $ins->startTransaction($path);
                    $ins->hasTrans = true;
                }
                // trace('AppInit');
                $ins->startSegment('app', Inspector::summaryInfo());
            }
        }
    }
}