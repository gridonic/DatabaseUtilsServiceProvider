# DatabaseUtilsServiceProvider

[![Build Status](https://travis-ci.org/gridonic/DatabaseUtilsServiceProvider.svg?branch=master)](https://travis-ci.org/gridonic/DatabaseUtilsServiceProvider)

This is a simple collection of utilities for your database for Silex and Doctrine DBAL.


## Install

As usual, just include `gridonic/database-utils-service-provider` in your `composer.json` , and register the service.

```php
$app->register(new \Gridonic\Provider\DatabaseUtilsServiceProvider(), array(
    'database_utils.fixtures'       => PATH_RESOURCES . '/fixtures/*.yml',
    'database_utils.password_keys'  => array('password'),
    'database_utils.security.salt'  => 'abcd',
));
```

### Parameters

An overview of the possible parameters

#### `database_utils.fixtures`
*required for fixtures*
All your fixtures-files.

#### `database_utils.password_keys`
*optional* An array of keys of table-columns, in which you are saving passwords. The values will be automatically encoded before insert.

#### `database_utils.security.salt`
*required for `database_utils.password_keys`* To encode the passwords, we use this salt.

## Commands
When you have registered the [ConsoleServiceProvider](https://github.com/gridonic/ConsoleServiceProvider) correct, you can use the following commands in your console.

### `database:drop`
Clears your database

### `database:reset`
Resets your database. Means:
* Drops your database
* Migrates the database (uses [MigrationServiceProvider](https://github.com/gridonic/MigrationServiceProvider))
* Loads example-data into your database

### `database:fixtures:load`
Loads example-data from your fixtures-files into the database.

when you set the password_keys and the salt, all the values for the password_keys (p.E. `1234` as `password`) will be encoded before insert.
All the passwords will be encoded by the `Silex\Provider\SecurityProvider`. You have to register the SecurityProvider before you can use this function.

#### Example 01_test.yml
```
test:
    -
        id: 1
        created: 1000000000
        username: abc
        email: abc@abc.com
        password: 1234
    -
        id: 2
        created: 1000000001
        username: def
        email: def@abc.com
        password: 1234
```
#### Example to register the SecurityProvider
```php
$app->register(new Silex\Provider\SecurityServiceProvider(), array(
    'security.firewalls' => array(
        'private' => array(
            'pattern' => '^/admin',
            'http' => true,
            'users' => array(
                'admin' => array('ROLE_ADMIN', 'ASv5vPSea0zB3EIpIB/mLOFAxkMIfh1EkTozyenPTZa0mGAiTC3n+mCAEdcYiITruuPaFb6GWFDiyF5fvJtqOg=='),
            ),
        ),
    ),
));
```


## Licence
The DatabaseUtilsServiceProvider is licensed under the [MIT license](LICENSE).
