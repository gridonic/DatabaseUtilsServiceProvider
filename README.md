# Migrations

This is a simple utils service for your database. We are using Silex and Doctrine, so this `DatabaseUtilsServiceProvider` is based on it.
Additionally, you can include


## Install

As usual, just include `gridonic/database-utils-service-provider` in your `composer.json` , and register the service.

```php
$app->register(new \Gridonic\Provider\DatabaseUtilsServiceProvider(), array(
));
```

## Licence
The DatabaseUtilsServiceProvider is licensed under the [MIT license](LICENSE).
