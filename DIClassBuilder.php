<?php
/**
 * Created by PhpStorm.
 * User: Yarmaliuk Mikhail
 * Date: 08.06.2017
 * Time: 9:59
 */

namespace MP\DIMagick;

use Yii;
use yii\base\InvalidConfigException;
use yii\db\ActiveRecord;
use yii\di\Container;
use yii\di\NotInstantiableException;
use yii\web\NotFoundHttpException;

/**
 * Class    DIClassBuilder
 * @package MP\DIMagick
 * @author  Yarmaliuk Mikhail
 * @version 1.0
 */
abstract class DIClassBuilder
{
    const EMPTY = 'buildDIDefault';

    /**
     * Build AR model
     *
     * @param array     $buildParams
     * @param \Closure  $selfClosure
     * @param array     $params
     * @param array     $config
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public static function build(array $buildParams = [], \Closure $selfClosure = NULL, array $params = [], array $config = [])
    {
        $id            = NULL;
        $model         = NULL;
        $className     = $buildParams['className'] ?? array_search($selfClosure, Yii::$container->getDefinitions());
        $requestFields = $buildParams['requestFields'] ?? ['id'];
        $errorMessage  = $buildParams['errorMessage'] ?? NULL;

        if (!$className) {
            throw new InvalidConfigException(Yii::t('app', 'The class for dependency is not specified.'));
        }

        $index = array_search(self::EMPTY, $params);

        if (!self::checkNeedResolveClass($className) || $index !== false) {
            if ($index !== false) {
                unset($params[$index]);
            }

            return Yii::$container->buildClass($className, $params, $config, $selfClosure);
        }

        $field = !empty($className::primaryKey()[0]) ? $className::primaryKey()[0] : NULL;

        if (!empty($requestFields)) {
            foreach ($requestFields as $field_ => $requestField) {
                if (($id = self::getRequestParam($requestField))) {
                    $field = is_int($field_) ? $field : $field_;
                    break;
                } elseif ((!is_numeric($field_) && ($id = self::getRequestParam($field_)))) {
                    $field = $requestField;
                    break;
                }
            }
        }

        if ($id && $field && method_exists($className, 'find')) {
            $model = $className::find()->where([$field => $id])->one();
        }

        if (!$model instanceof $className) {
            throw new NotInstantiableException($className, $errorMessage, 0, new NotFoundHttpException($errorMessage));
        }

        return $model;
    }

    /**
     * Get request param
     *
     * @param $request
     *
     * @return mixed
     */
    private static function getRequestParam($name)
    {
        // For console app
        if (Yii::$app->request instanceof yii\console\Request) {
            preg_match('/-' . $name . '=([^\s]+)/', implode(' ', Yii::$app->request->params), $matches);

            return !empty($matches[1]) ? $matches[1] : NULL;
        }

        // For web app
        return Yii::$app->request->post($name) ? : Yii::$app->request->get($name);
    }

    /**
     * Check if need resolve class
     *
     * @param string $className
     *
     * @return bool
     */
    private static function checkNeedResolveClass(string $className): bool
    {
        $resolveParams = Yii::$container->getDIBuilderResolveParams();

        if (!empty($resolveParams)) {
            foreach ($resolveParams as $resolveParam) {
                if (isset($resolveParam['class']) && $resolveParam['class'] === $className) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Bind dependency class
     *
     * @param array $params
     *
     * @return \Closure
     */
    public static function bind(array $params)
    {
        $selfClosure = NULL;

        $closure = function (Container $container, $p, $c) use ($params, &$selfClosure) {
            return $container->invoke([self::class, 'build'], [
                'buildParams' => $params,
                'selfClosure' => $selfClosure,
                'params'      => $p,
                'config'      => $c,
            ]);
        };

        $selfClosure = $closure;

        if (!Yii::$container->getBehavior('finder')) {
            Yii::$container->attachBehavior('finder', DIContainerBehavior::class);
        }

        return $closure;
    }
}