<?php
/**
 * Created by PhpStorm.
 * User: Yarmaliuk Mikhail
 * Date: 01.11.2017
 * Time: 12:00
 */

namespace  MP\DIMagick;

use Yii;
use yii\base\Controller;
use yii\base\InlineAction;
use yii\base\InvalidConfigException;
use yii\di\NotInstantiableException;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;

/**
 * Trait    ActionDITrait
 * @package MP\DIMagick
 * @author  SamMousa
 * @author  Yarmaliuk Mikhail
 * @version 1.0
 *
 * @see     https://github.com/SAM-IT/yii2-magic
 *
 * @mixin Controller
 */
trait ActionDITrait
{
    /**
     * @see https://github.com/SAM-IT/yii2-magic/blob/master/src/Traits/ActionInjectionTrait.php#L19
     * @see https://github.com/yiisoft/yii2/issues/9476
     * @inheritdoc
     */
    public function bindActionParams($action, $params)
    {

        if ($action instanceof InlineAction) {
            $callable = [$this, $action->actionMethod];
        } else {
            $callable = [$action, 'run'];
        }

        $actionParams = [];

        try {
            $args = Yii::$container->resolveCallableDependencies($callable, $params);
        } catch (InvalidConfigException $e) {
            if ($e->getPrevious() instanceof NotFoundHttpException) {
                throw $e->getPrevious();
            }

            throw new BadRequestHttpException($e->getMessage());
        }

        foreach ((new \ReflectionMethod($callable[0], $callable[1]))->getParameters() as $i => $param) {
            $actionParams[$param->getName()] = $args[$i];
        }

        if (property_exists($this, 'actionParams')) {
            $this->actionParams = $actionParams;
        }

        // Dont put injected params in requestedParams, this breaks the debugger.
        foreach ($actionParams as $key => $value) {
            if (is_object($value)) {
                if (Yii::$app->has($key, true) && $value === Yii::$app->get($key)) {
                    $value = "Application component: $key";
                } else {
                    $value = "DI: " . get_class($value);
                }
            }

            Yii::$app->requestedParams[$key] = $value;
        }

        return $args;
    }

    /**
     * @inheritdoc
     *
     * @throws NotFoundHttpException
     * @throws NotInstantiableException
     */
    public function runAction($id, $params = [])
    {
        try {
            return parent::runAction($id, $params);
        } catch (NotInstantiableException $exception) {
            if ($exception->getPrevious() instanceof NotFoundHttpException) {
                throw $exception->getPrevious();
            }

            throw $exception;
        }
    }
}