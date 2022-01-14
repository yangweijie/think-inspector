<?php
declare (strict_types = 1);

namespace think\Inspector;

use think\App;
use think\Console as ParentConsole;
use think\console\Input;
use think\console\Output;

class Console extends ParentConsole
{
    private $inspector = null;
    // protected $app = null;

    /**
     * 初始化
     */
    protected function initialize()
    {
        parent::initialize();
        $this->inspector = $this->app->inspector;
        if($this->inspector->hasTrans == false){
            $path = Inspector::getPath($this->inspector->options['single']);
            $this->inspector->startTransaction($path);
        }
        // trace($this->inspector->segments);
    }

    /**
     * 加载指令
     * @access protected
     */
    protected function loadCommands(): void
    {
        $this->inspector->addSegment(function () {
            parent::loadCommands();
        }, 'loadCommands', '加载全部命令');
    }

    /**
     * 执行指令
     * @access public
     * @param Input $input
     * @param Output $output
     * @return int
     */
    public function doRun(Input $input, Output $output)
    {
        $ret   = null;
        $label = sprintf("运行【%s】命令", $input->getFirstArgument());
        $this->inspector->addSegment(function () use ($input, $output, &$ret){
            // trace($input);
            $ret = parent::doRun($input, $output);
        }, 'runCmd', $label);
        $data = [
            'Server/Request Data' => $this->app->request->server(),
        ];

        if($this->inspector->currentTransaction() && $this->inspector->currentTransaction() != null){
            $this->inspector->currentTransaction()->addContext('请求信息', $data);
            $this->inspector->currentTransaction()->addContext('summary', Inspector::summaryInfo());
        }
    }

    public function __destruct(){

    }
}