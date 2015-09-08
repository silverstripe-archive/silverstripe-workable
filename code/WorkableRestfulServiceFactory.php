<?php

/**
 * Factory to build a RestfulService object for workable. This is needed for three reasons:
 *
 * - It sets the $apiKey by preferring the constant, but falling back on the config layer
 * - RestfulService needs a URL in its constructor, and this is a computed value based on the
 *   static workable API base URL plus the user's subdomain
 * - It makes various boilerplate configurations to RestfulService, e.g. cache expiry, auth headers.
 */
class WorkableRestfulServiceFactory implements SilverStripe\Framework\Injector\Factory {

	/**
	 * Create the RestfulService (or whatever dependency you've injected)
	 * @param  string $service 
	 * @param  array  $params 
	 * @return RestfulService
	 */
    public function create($service, array $params = []) {;
    	$config = Workable::config();
    	$subdomain = $config->subdomain;

    	if(!$subdomain) {
    		throw new RuntimeException('You must set a Workable subdomain in the config (Workable.subdomain)');
    	}

        $rest = new $service(
        	sprintf('https://www.workable.com/spi/v3/accounts/%s/',$subdomain),
        	$config->cache_expiry
        );

        if(defined('WORKABLE_API_KEY')) {
        	$apiKey = WORKABLE_API_KEY;
        }
        else {
        	$apiKey = Config::inst()->get('Workable','apiKey');
        }

        if(!$apiKey) {
        	throw new RuntimeException('You must define an API key for Workable. Either use the WORKABLE_API_KEY constant or set Workable.apiKey in the config');
        }

        $rest->httpHeader("Authorization:Bearer $apiKey");
        $rest->httpHeader("Content-Type: application/json");
        
        return $rest;
    }
}