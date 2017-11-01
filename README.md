DI Action Magick Yii2
===========================
Improvement for Yii2 that add dependency injection in action methods and provide model binding.

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist matthew-p/yii2-di-magic "*"
```

or add

```
"matthew-p/yii2-di-magic": "*"
```

to the require section of your `composer.json` file.

Usage
-----

Once the extension is installed, simply use it in your code by:

In main.php config add:
```php
return [
    ...
        'container'  => [
            'definitions' => [
                ARModel::class  => function (Container $container) {
                    return $container->invoke([DIClassBuilder::class, 'build'], [
                        'className'     => ARModel::class,
                        'requestFields' => ['id', 'model_id'], // array request fields (find by primaryKey)
                        'errorMessage'  => Yii::t('app', 'Model not found'),
                    ]);
                },
                
                ARModel2::class  => function (Container $container) {
                    return $container->invoke([DIClassBuilder::class, 'build'], [
                        'className'     => ARModel2::class,
                        'requestFields' => ['requestField' => 'modelColumn', 'requestField2' => 'modelColumn', 'requestFiled'], // find by primary key or modelColumn
                        'errorMessage'  => Yii::t('app', 'Model not found'),
                    ]);
                },
            ],
        ],
    ...
];
```

And use:
```php
class ModelController extends Controller
{
    use ActionDITrait;

    /**
     * View model.
     *
     * @param  ARModel $model
     *
     * @return string
     * @throws NotFoundHttpException
     */
    public function actionView(ARModel $model): string
    {
        ...
    }
}
```

Run: /model/view?id=1 or /model/view?model_id=1 

That's all. Check it.
