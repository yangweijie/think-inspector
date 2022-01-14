<?php

namespace think\Inspector;

use think\facade\Event;
use think\Service;
use think\facade\Db;
use think\facade\Config;

class InspectorService extends Service
{
    public function register()
    {
        $this->app->bind('inspector', Inspector::class);
        $this->app->bind('think\exception\Handle', ExceptionInspectorHandle::class);
    }

    public function startTransaction(){
        // trace(__FUNCTION__);
        $inspector    = $this->app->inspector;
        $path = Inspector::getPath($inspector->options['single']);
        // trace('service_path '. $path);
        if($path){
            $ret = $inspector->startTransaction($path);
        }
    }

    public function boot(){
        $inspector = $this->app->inspector;

        if($inspector->hasTrans == false){
            $this->startTransaction();
        }

        if($inspector->options['enable'] == true){
            $data = [
                'GET Data'            => $this->app->request->get(),
                'POST Data'           => $this->app->request->post(),
                'Server/Request Data' => $this->app->request->server(),
            ];

            if($inspector->currentTransaction() && $inspector->currentTransaction() != null){
                $inspector->currentTransaction()->addContext('请求信息', $data);
            }

            Event::listen('HttpEnd', function() use($inspector){
                // trace('HttpEnd');
                // trace($inspector->segments);
                $inspector->end('app');
                if($inspector->currentTransaction() && $inspector->currentTransaction() != null){
                    $inspector->currentTransaction()->addContext('总结', Inspector::summaryInfo());
                    $inspector->currentTransaction()->setResult(200);
                }
            });
            Db::listen(function($sql, $runtime, $master) use($inspector){
                $driver = Config::get('database.type', 'mysql');
                if(is_numeric($runtime)){
                    $label  = sprintf('%s %s', $sql, is_null($master)? '': "分布式 | {$master}");
                    $start  = microtime(true) - $runtime;
                    $inspector->startSegment($driver, $label)
                        ->start($start)
                        ->end($runtime*1000);
                    // $inspector->startSegment($driver, $label);
                }else{

                }
            });
        }
        // trigger_sql
    }
}