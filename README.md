Yii2 Sms Aero
======
https://smsaero.ru/api/v1/
Integration of SMS-messages to yii2 application

Installation
------------

### One
The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```sh
php composer.phar require --prefer-dist sersid/yii2-smsaero "dev-master"
```

or add

```
"sersid/yii2-smsaero": "dev-master"
```

to the require section of your `composer.json` file.



### Two

```php
$config = [
    ...
    'components' => [
        ...
        'sms' => [
            'class' => 'sersid\smsaero\SmsAero',
            'user' => 'username',
            //'password' => '*****',
            'api_key' => 'paste_your_api_key' // use api_key or password
            'sender' => 'SMS Aero', // default sender
        ],
    ]
];
```

### Three
Read the documentation
http://smsaero.ru/api/



Usage
-----

Once the extension is installed, simply use it in your code by  :

#### Send message
```php
Yii::$app->sms->send('798765543210', 'Message'); //@see Send message method
```

#### Checking the status of the sent message
```php
Yii::$app->sms->status(123456);
```

#### Balance
```php
Yii::$app->sms->balance(); // ['balance' => '30.00']
```

#### Available senders
```php
Yii::$app->sms->senders(); // ['INFORM', 'MY_SENDER', '...']
```

#### Request new signature
```php
Yii::$app->sms->sign('new sender');
```