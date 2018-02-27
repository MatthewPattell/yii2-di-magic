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
                ARModel::class  =>  DIClassBuilder::bind([
                    'className'     => ARModel::class,     // optional
                    'requestFields' => ['id', 'model_id'], // array request fields (find by primaryKey)
                    'errorMessage'  => Yii::t('app', 'Model not found'),
                ]);
                
                ARModel2::class  => DIClassBuilder::bind([
                    'requestFields' => ['requestField' => 'modelColumn', 'requestField2' => 'modelColumn', 'requestFiled'], // find by primary key or modelColumn
                    'errorMessage'  => Yii::t('app', 'Model not found'),
                ]);
                
                ARModel3::class  => DIClassBuilder::bind(); // find by id default
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
        // A search was made in the database. var_dump($model);
        ...
        // Get class instance. 'DIClassBuilder::EMPTY' key will be removed, it flag for get new instance 
        $classInstance = Yii::$container->get(ARModel::class, [DIClassBuilder::EMPTY], ['param' => 'value']);
        
        // Get duplicate $model
        $duplicateModel = Yii::$container->get(ARModel::class);
        
        // Get some class instance. 'DIClassBuilder::EMPTY' key not need, because class not specified in action params
        $someModel = Yii::$container->get(SomeModel::class);
    }
    
    /**
     * Sample action.
     *
     * @param  ARModel|NULL $model not required
     *
     * @return void
     */
    public function actionSample(ARModel $model = NULL): void
    {        
        if (is_null($model)) {
            $model = Yii::$container->get(ARModel::class, [DIClassBuilder::EMPTY]);
        }
    }
}
```

Run: /model/view?id=1 or /model/view?model_id=1 or /model/sample?id=99999 

That's all. Check it.
