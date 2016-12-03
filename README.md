# dojoModule


Zend Framework 2 module for Dojo 1.x integration

## Use

### AbstractJsonRestController
The `AbstractJsonRestController` requires a the hydrator manager to be injected into its constructor.
Its recommended to use `LazyControllerAbstractFactory` as a factory for concrete controller implementations.

For example, replacing `AbstractJsonRestController` with the name of your subclass:
```php
'controllers' => [
    'factories' => [
        AbstractJsonRestController::class => LazyControllerAbstractFactory::class
    ]
]
```

### View Models

#### Dojo

In a view:
```php
$this->dojo()
    ->requireModule();
    
    
//or

$this->plugin('dojo')
    ->requireModule();
```