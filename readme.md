# Fyler Package

## Purpose

This package is a SORAD service for streamlining file processing and handling.

## Install via Composer

Add the following to your "require" schema:

```
"require": {
     "priskz/fyler": "~0.0.1"
}
```

Run ```composer install```

Add ```Fyler' => 'Fyler\Laravel\Facade',``` to the ```'aliases'``` aka facades in ```/app/laravel/config/app.php``` to register the newly added service facade.