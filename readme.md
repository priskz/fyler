# Fyler Package

## Purpose

This package is a SORAD service for streamlining file processing and handling.

## Install via Composer

Add the following to your "require" schema:

```
"require": {
     "priskz/fyler": "1.0.*"
}
```

Run ```composer update```

Add ```Fyler' => 'Fyler\Laravel\Facade',``` to the ```'aliases'``` aka facades in ```/app/laravel/config/app.php``` to register the newly added service facade.