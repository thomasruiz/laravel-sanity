# Prerequisites
## Install

This package can be installed through Composer.

``` bash
composer require thomasruiz/laravel-sanity
```
You must install this service provider.

``` php
// config/app.php
'provider' => [
    LaravelSanity\LaravelSanityServiceProvider::class,
];
```

# Usage
## Example

```
$ php artisan sanity production
[app.env] expected to be "production", "local" found
[app.url] expected to NOT match "/localhost/", "http://localhost" found
[mail.driver] expected to NOT be "log", "log" found
```
