# Cache contents of entire route in Laravel
This is the Laravel 7.0+ / PHP 7.2+ package, which provides the ability to cache routes for the allotted time.

## Installation

```
$ composer require stounhandj/laravel-cache-route
```
Or
```json
{
    "require": {
        "stounhandj/laravel-cache-route": "^v1.1"
    }
}
```
## Usage
Add middleware to the file kernel.php:
```php
'cache.page' => \StounhandJ\LaravelCacheRoute\Middleware\CacheRoteMiddleware::class,
```
Now, use the middleware to cache the HTML output of an entire page from your route like so:

1. In your route:

   ```php
   Route::get('/', function () {
        //
   })->middleware("cache.page")
   ```

   You may also use route groups. Please look up Laravel documentation on Middleware to learn more
   [here](https://laravel.com/docs/7.x/middleware)
## Configuration Options
You can configure the TTL (Time-To-Live) to cast per second:
1. In your route:
   ```php
   Route::get('/', function () {
        //
   })->middleware("cache.page:10")
   ```
2. Environment (On all routes at once):
    ```env
    CACHE_TTL=10
    ```
   
## Thoughts
Be VERY cautions when using a whole page cache such as this. Remember contents of the cache are visible to ALL your users. 
1. For, "mostly static" content, go for it!
2. For, "mostly dynamic" content or heavily user-customized content, AVOID this strategy. User specific information is gathered server side. So, you essentially WANT to hit the server.

__Good rule of thumb__: If two different users see different pages on hitting the same URL, DO NOT cache the output using this strategy. An alternative may be to cache database queries.