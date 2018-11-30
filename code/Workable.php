<?php

namespace SilverStripe\Workable;

use RuntimeException;
use GuzzleHttp\Client;
use Psr\Log\LoggerInterface;
use SilverStripe\Core\Convert;
use SilverStripe\ORM\ArrayList;
use SilverStripe\Core\Extensible;
use SilverStripe\Core\Environment;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;

class Workable
{
    use Extensible;
    use Injectable;
    use Configurable;

    private static $cache_expiry = 3600;

    /**
     * Gets all the jobs from the Workable API
     * @param  array  $params Array of params, e.g. ['state' => 'published']
     * @return ArrayList
     */
    public function getJobs($params = [])
    {
        $list = ArrayList::create();
        $response = $this->callRestfulService('jobs', $params);

        if ($response && isset($response['jobs']) && is_array($response['jobs'])) {
            foreach ($response['jobs'] as $record) {
                $list->push(WorkableResult::create($record));
            }
        }

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
        $apiKey = Environment::getEnv('WORKABLE_API_KEY');
        $subdomain = static::config()->subdomain;
        $logger = Injector::inst()->get(LoggerInterface::class);

        if (!$apiKey) {
            throw new RuntimeException('WORKABLE_API_KEY Environment variable not set');
        }

        if (!$subdomain) {
            throw new RuntimeException(
                'You must set a Workable subdomain in the config (SilverStripe\Workable\Workable.subdomain)'
            );
        }

        $client = new Client([
            'base_uri' => sprintf('https://%s.workable.com/spi/v3/', $subdomain),
            'headers' => [
                'Authorization' => sprintf('Bearer %s', Environment::getEnv('WORKABLE_API_KEY')),
            ]
        ]);
        $response = $client->request($method, $url, $params);

        return Convert::json2array($response->getBody());
    }
}
