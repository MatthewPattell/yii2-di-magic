<?php
/**
 * Created by PhpStorm.
 * User: Yarmaliuk Mikhail
 * Date: 08.06.2017
 * Time: 9:59
 */

namespace MP\DIMagick;

use yii\db\ActiveRecord;
use yii\di\Container;
use yii\di\NotInstantiableException;
use yii\web\NotFoundHttpException;
use Yii;

/**
 * Class    DIClassBuilder
 * @package MP\DIMagick
 * @author  Yarmaliuk Mikhail
 * @version 1.0
 */
abstract class DIClassBuilder
{
    /**
     * Build AR model
     *
     * @param Container           $container
     * @param string|ActiveRecord $className
     * @param array               $requestFields
     * @param null|string         $errorMessage
     *
     * @return mixed
     * @throws NotFoundHttpException
     */
    public static function build(Container $container, $className, $requestFields = ['id'], $errorMessage = NULL)
    {
        $id      = NULL;
        $model   = NULL;
        $field   = !empty($className::primaryKey()[0]) ? $className::primaryKey()[0] : NULL;

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
        return Yii::$app->request->post($name) ? : Yii::$app->request->get($name);
    }
}