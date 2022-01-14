<h1 align="center"> think-inspector </h1>

<p align="center"> inspector package for thinkphp.</p>


## Installing

```shell
$ composer require yangweijie/think-inspector -vvv
```

## Usage

### 配置

#### key

在 https://docs.inspector.dev/ 注册后创建应用的 key 用于区分不同应用 前后端 应该也是独立应用

#### enable

是否启用监听

#### max_items

每个监听会话里 允许添加的最大片段数。 超过 添加不进去

#### env

目前没用到  计划 用于区分 不同环境的


#### single

是否区分 不同url 命令

true 意味着 启动监听时的path 是动态独立 ， 然后 monitor 面版 会通过这些path 去区分 不同的监听 分组

false web 请求 path 为 WEB 命令行 为 命令行 这样方便查找监听记录 只用 在 WEB 或 命令行里 再按时间 进行查看

### 注入

在本服务中 向 app 注入了 inspector 对象 (自定义类) inspector 对象的 ins 属性 是原始 inspector-apm 的对象

inspector 可以调用 inspector-apm 里 inspector 类的方法。

为了 方便 设置 框架层的 片段 覆写了 startSegment 和 end 方法 。

只需要 `$this->app->inspector->startSegment('type', 'label');`  到 需要结束时 `$this->app->inspector->end('type');` 即可

当然如果你 业务中手动添加 监听的 片段 

也可以 类似于 库里 console 覆写 doRun 方法
~~~
$ret   = null;
$label = sprintf("运行【%s】命令", $input->getFirstArgument());
$this->inspector->addSegment(function () use ($input, $output, &$ret){
    // trace($input);
    $ret = parent::doRun($input, $output);  // 包裹自己的代码
}, 'runCmd', $label);

~~~

#### context

参考 本库实现的 

~~~
if($inspector->currentTransaction() && $inspector->currentTransaction() != null){
    $inspector->currentTransaction()->addContext('请求信息', $data);
}
~~~

同样 上下文 加入时 会产生多个tab 最好不用 timeline 、url 、request 系统内置的。


### 命令行监听

目前 命令行 监听 需要 开启 应用 全局 Provid.php 里 配置 映射的类 

~~~

<?php
use app\ExceptionHandle;
use app\Request;
use think\Inspector\Console;

// 容器Provider定义文件
return [
    'think\Console'          => Console::class,
    'think\exception\Handle' => ExceptionHandle::class,
    'think\Request'          => Request::class,
];

~~~

否则不生效，尝试 服务 register 方法里 bind  无效。


### 事件

本库自定义了 一个 AppInit 类 动态绑定了 框架的 AppInit

对于 web 应用 会经过 AppInit、HttpRun、HttpEnd 等事件

请求信息 在 服务 boot 阶段就可以拿到 就不需要在 HttpRun 里 开启了。

web 中会多一个summary 信息 显示 请求耗时 内存 加载文件数量 以前在trace 为 console 里看到的信息

并且 Db 事件 记录了 每条sql 的时间 和原始执行语句 方便后面 根据 timeline 里 长短 来进行性能优化

而 命令行 不经过 应用的事件 只 记录了 加载全部命令 和执行当前命令的时间 

## TODO

[x]测试tp5


## Contributing

You can contribute in one of three ways:

1. File bug reports using the [issue tracker](https://github.com/yangweijie/think-inspector/issues).
2. Answer questions or fix bugs on the [issue tracker](https://github.com/yangweijie/think-inspector/issues).
3. Contribute new features or update the wiki.

_The code contribution process is not very formal. You just need to make sure that you follow the PSR-0, PSR-1, and PSR-2 coding guidelines. Any new code contributions must be accompanied by unit tests where applicable._

## License

MIT