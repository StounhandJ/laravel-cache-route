<?php

namespace StounhandJ\LaravelCacheRoute\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;
use Psr\SimpleCache\InvalidArgumentException;

class CacheRoteMiddleware
{
    protected $request;
    protected $cacheKey;
    protected $ttl;

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param mixed ...$ttl
     * @return mixed
     * @throws InvalidArgumentException
     */
    public function handle(Request $request, Closure $next, ...$ttl)
    {
        $this->request = $request;
        $this->cacheKey = $this->makeCacheKey($request->fullUrl());
        $this->ttl = $this->getCacheTTL($ttl);
        return $this->getResponse($next);
    }

    protected function getCacheTTL($args)
    {
        if (count($args) != 0 && is_numeric($args[0]))
            return (int)$args[0];

        return env('CACHE_TTL', 10);
    }

    /**
     * @param $url
     * @return string
     */
    protected function makeCacheKey($url): string
    {
        return 'route:' . $url;
    }


    /**
     * @throws InvalidArgumentException
     * @throws Exception
     */
    protected function getResponse(Closure $next)
    {
        if (!Cache::store("memcached")->has($this->cacheKey)) {
            $response = $next($this->request);
            $this->storeInCache($this->cacheKey, $response->getContent(), $this->ttl);
            $this->storeInCache($this->cacheKey.".headers", $response->headers, $this->ttl+5);
            return $response;
        }

        $response = new Response($this->valueInCache($this->cacheKey));
        $response->headers = $this->valueInCache($this->cacheKey.".headers");
        return $response;
    }


    /**
     * @param $cacheKey
     * @param $pageContents
     * @param $ttl
     * @throws Exception
     */
    protected function storeInCache($cacheKey, $pageContents, $ttl)
    {
        try {
            Cache::store("memcached")->put($cacheKey, $pageContents, $ttl);
        } catch (Exception $ex) {
            throw new Exception('Sorry. Response could not be cached.');
        }
    }

    /**
     * @param $key
     * @return mixed
     * @throws InvalidArgumentException
     * @throws Exception
     */
    protected function valueInCache($key)
    {
        try {
            return Cache::store("memcached")->get($key);
        } catch (Exception $ex) {
            throw new Exception('Sorry. Response could not be cached.');
        }
    }
}
