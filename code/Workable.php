<?php

namespace SilverStripe\Workable;

use RuntimeException;
use Psr\Log\LoggerInterface;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Core\Flushable;
use SilverStripe\View\ArrayData;
use SilverStripe\Core\Extensible;
use Psr\SimpleCache\CacheInterface;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;

class Workable implements Flushable
{
    use Extensible;
    use Injectable;
    use Configurable;

    /**
     * Reference to the RestfulService dependency
     * @var RestfulService
     */
    protected $restulService;

    /**
     * Constructor, inject the restful service dependency
     * @param RestfulService $restfulService
     */
    public function __construct($restfulService)
    {
        $this->restfulService = $restfulService;
    }

    /**
     * Gets all the jobs from the Workable API
     * @param  array  $params Array of params, e.g. ['state' => 'published']
     * @return ArrayList
     */
    public function getJobs($params = [])
    {
        $cache = Injector::inst()->get(CacheInterface::class . '.workable');
        $cacheKey = 'Jobs';
        $list = ArrayList::create();
        $response = $this->callRestfulService('jobs', $params);

        if ($cache->has($cacheKey)) {
            return $cache->get($cacheKey);
        }

        if ($response && isset($response['jobs']) && is_array($response['jobs'])) {
            foreach ($response['jobs'] as $record) {
                $list->push(WorkableResult::create($record));
            }
        }

        $cache->set($cacheKey, $list);

        return $list;
    }

    /**
     * Gets information on a specific job form the Workable API
     * @param  string $shortcode Workable shortcode for the job, e.g. 'GROOV005'
     * @param  array  $params    Array of params, e.g. ['state' => 'published']
     * @return WorkableResult|null
     */
    public function getJob($shortcode, $params = [])
    {
        $job = null;
        $cache = Injector::inst()->get(CacheInterface::class . '.workable');
        $cacheKey = 'Job-' . $shortcode;
        $response = $this->callRestfulService('jobs/' . $shortcode, $params);

        if ($cache->has($cacheKey)) {
            return $cache->get($cacheKey);
        }

        if ($response && isset($response['id'])) {
            $job = WorkableResult::create($response);
        }

        $cache->set($cacheKey, $job);

        return $job;
    }

    /**
     * Gets all the jobs from the workable API, populating each job with its full data
     * Note: This calls the API multiple times so should be used with caution.
     * @param  array  $params Array of params, e.g. ['state' => 'published']
     * @return ArrayList
     */
    public function getFullJobs($params = [])
    {
        $cache = Injector::inst()->get(CacheInterface::class . '.workable');
        $cacheKey = 'FullJobs';
        $list = ArrayList::create();
        $response = $this->callRestfulService('jobs', $params);

        if ($cache->has($cacheKey)) {
            return $cache->get($cacheKey);
        }

        if ($response && isset($response['jobs']) && is_array($response['jobs'])) {
            foreach ($response['jobs'] as $record) {
                $job = $this->getJob($record['shortcode'], $params);
                $list->push($job);
            }
        }

        $cache->set($cacheKey, $list);

        return $list;
    }

    /**
     * Wrapper method to configure the RestfulService, make the call, and handle errors
     * @param  string $url
     * @param  array  $params
     * @param  string $method
     * @return array  JSON
     */
    public function callRestfulService($url, $params = [], $method = 'GET')
    {
        $response = $this->restfulService->request($method, $url, ['query' => $params]);

        return Convert::json2array($response->getBody());
    }

    /**
     * Clear the cache when flush is called
     */
    public static function flush()
    {
        $cache = Injector::inst()->get(CacheInterface::class . '.workable');
        $cache->clear();
    }
}
