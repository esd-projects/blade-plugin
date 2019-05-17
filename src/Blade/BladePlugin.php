<?php
/**
 * Created by PhpStorm.
 * User: 白猫
 * Date: 2019/5/17
 * Time: 14:51
 */

namespace ESD\Plugins\Blade;


use ESD\BaseServer\Server\Context;
use ESD\BaseServer\Server\PlugIn\AbstractPlugin;
use ESD\BaseServer\Server\Server;

class BladePlugin extends AbstractPlugin
{
    /**
     * @var Blade
     */
    protected $blade;
    /**
     * @var BladeConfig|null
     */
    private $bladeConfig;

    /**
     * BladePlugin constructor.
     * @param BladeConfig|null $bladeConfig
     * @throws \ReflectionException
     * @throws \DI\DependencyException
     */
    public function __construct(?BladeConfig $bladeConfig = null)
    {
        parent::__construct();
        if ($bladeConfig == null) $bladeConfig = new BladeConfig();
        $this->bladeConfig = $bladeConfig;
    }

    /**
     * 获取插件名字
     * @return string
     */
    public function getName(): string
    {
        return "Blade";
    }

    /**
     * 初始化
     * @param Context $context
     * @return mixed
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     * @throws \ESD\BaseServer\Server\Exception\ConfigException
     * @throws \ESD\BaseServer\Exception
     */
    public function beforeServerStart(Context $context)
    {
        $this->bladeConfig->merge();
        if (empty($this->bladeConfig->getCachePath())) {
            $cacheDir = Server::$instance->getServerConfig()->getCacheDir() . "/blade";
            $this->bladeConfig->setCachePath($cacheDir);
        }
        $this->bladeConfig->merge();
        $cacheDir = $this->bladeConfig->getCachePath();
        if (!is_dir($cacheDir)) {
            mkdir($cacheDir, 0777, true);
        }
        $this->blade = new Blade($this->bladeConfig->getCachePath());
        foreach ($this->bladeConfig->getNamespace() as $value) {
            $this->blade->addNamespace($value->getName(), $value->getPath());
        }
        $this->setToDIContainer(Blade::class, $this->blade);
    }

    /**
     * 在进程启动前
     * @param Context $context
     * @return mixed
     */
    public function beforeProcessStart(Context $context)
    {
        $this->ready();
    }
}