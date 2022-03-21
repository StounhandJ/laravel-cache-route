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
        if (!$this->okToCache($request)) {
            return $next($request);
        }
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
     * Never cache non-GET requests
     * Do not cache if CACHE_ENABLED env variable is set to false
     * @param $request
     * @return bool
     */
    protected function okToCache($request): bool
    {
        if (!$request->isMethod('get')) {
            return false;
        }
        return env('CACHE_ENABLED', true);
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
            return $response;
        }
        return new Response(Cache::store("memcached")->get($this->cacheKey));
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
}
