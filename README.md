Yii2 Megaplan Auth Client
=========================
Provides ability to auth users using Megaplan Auth API
Specification can be found [there](http://help.megaplan.ru/API_authorization).

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist leammas/yii2-megaplanauth "*"
```

or add

```
"leammas/yii2-megaplanauth": "*"
```

to the require section of your `composer.json` file.

Requirements
------------
- Yii2
- PHP 5.4+
- Curl and php-curl installed

Usage
-----

Once the extension is installed, use it as application component :

```
    'components' => [
        ...
        'mpauth' => [
            'class' => 'leammas\yii2\megaplanauth\Megaplanauth',
            'url' => 'http://some.url',
            'timeout' => 10
        ],
        ...
```

The main method `authenticate` accepts two parameters `username` and `password`.
Use it somewhere in your form or user model validation.
It will return array with user data if succeed or throw MPAuthException with error message.

```php
    /**
     * @param $password
     * @return bool
     */
    public function validatePassword($password)
    {
        try
        {
            $success = Yii::$app->mpauth->authenticate($this->username, $password);
            // if you don't use Megaplan user id's or employee id's, just replace it with `return true;`
            return $success['EmployeeId'] == $this->id;
        }
        catch(MPAuthException $e)
        {
            Yii::trace('MPAuth Fail: ' . $e->getMessage());
            return false;
        }
    }
```