<?php
/**
 * Created by PhpStorm.
 * User: Yarmaliuk Mikhail
 * Date: 27.02.18
 * Time: 18:12
 */

namespace MP\DIMagick;

use Yii;
use yii\base\Behavior;
use yii\di\Container;

/**
 * Class    DIContainerBehavior
 * @package common\components
 * @author  Yarmaliuk Mikhail
 * @version 1.0
 */
class DIContainerBehavior extends Behavior
{
    /**
     * @var array
     */
    private $diBuilderResolveParams = [];

    /**
     * Set DI builder resolve params
     *
     * @param array $params
     *
     * @return void
     *
     * @see DIClassBuilder
     */
    public function setDIBuilderResolveParams(array $params): void
    {
        $this->diBuilderResolveParams = $params;
    }

    /**
     * Get DI builder resolve params
     *
     * @return array
     */
    public function getDIBuilderResolveParams(): array
    {
        return $this->diBuilderResolveParams;
    }

    /**
     * Build class
     *
     * @see Container::get
     *
     * @param string        $className
     * @param array         $params
     * @param array         $config
     * @param \Closure|NULL $closure
     *
     * @return object
     *
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    public function buildClass(string $className, array $params = [], array $config = [], \Closure $closure = NULL)
    {
        Yii::$container->setDefinitions([
            $className => $className,
        ]);

        $object = Yii::$container->get($className, $params, $config);

        if ($closure instanceof \Closure) {
            Yii::$container->setDefinitions([
                $className => $closure,
            ]);
        }

        return $object;
    }
}