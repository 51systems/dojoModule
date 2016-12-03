# dojoModule


Zend Framework 2 module for Dojo 1.x integration

## Use

### Dojo

In a view:
```php
$this->dojo()
    ->requireModule();
    
    
//or

$this->plugin('dojo')
    ->requireModule();
```