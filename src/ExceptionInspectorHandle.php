<?php

namespace think\Inspector;

use think\exception\Handle;
use think\Response;
use Throwable;

class ExceptionInspectorHandle extends Handle
{
    public function render($request, Throwable $e): Response
    {
        if(!isset($this->app->inspector)){
            $this->app->bind('inspector', Inspector::class);
        }
        // Inspector 记录异常
        if ($this->app->get('inspector') != null && $this->app->get('inspector')->options['enable']) {
            $this->app->inspector->reportException($e);
            if($this->app->inspector->currentTransaction() != null){
                $this->app->inspector->currentTransaction()->setResult($e->getCode());
            }
        }

        // 其他错误交给系统处理
        return parent::render($request, $e);
    }
}