<?php

namespace SilverStripe\Workable;

use SilverStripe\Core\Environment;
use SilverStripe\Workable\Workable;
use SilverStripe\Core\Injector\Factory;

/**
 * Factory to build a RestfulService object for workable. This is needed for three reasons:
 *
 * - It sets the $apiKey by preferring the constant, but falling back on the config layer
 * - RestfulService needs a URL in its constructor, and this is a computed value based on the
 *   static workable API base URL plus the user's subdomain
 * - It makes various boilerplate configurations to RestfulService, e.g. auth headers.
 */
class WorkableRestfulServiceFactory implements Factory
{
    /**
     * Create the RestfulService (or whatever dependency you've injected)
     * @return RestfulService
     */
    public function create($service, array $params = [])
    {
        $apiKey = Environment::getEnv('WORKABLE_API_KEY');
        $subdomain = Workable::config()->subdomain;

        if (!$apiKey) {
            throw new RuntimeException('WORKABLE_API_KEY Environment variable not set');
        }

        if (!$subdomain) {
            throw new RuntimeException(
                'You must set a Workable subdomain in the config (SilverStripe\Workable\Workable.subdomain)'
            );
        }

        return new $service([
            'base_uri' => sprintf('https://%s.workable.com/spi/v3/', $subdomain),
            'headers' => [
                'Authorization' => sprintf('Bearer %s', $apiKey),
            ],
        ]);
    }
}
