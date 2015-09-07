<?php

/**
 * Defines the Workable API wrapper
 *
 * @package  silverstripe/workable
 * @author  Aaron Carlino <aaron@silverstripe.com>
 */
class Workable extends Object {

	/**
	 * Reference to the RestfulService dependency
	 * @var RestfulService
	 */
	protected $restulService;

	/**
	 * Constructor, inject the restful service dependency
	 * @param RestfulService $restfulService
	 */
	public function __construct($restfulService) {
		$this->restfulService = $restfulService;

		parent::__construct();
	}

	/**
	 * Gets all the jobs from the Workable API
	 * @param  array  $params Array of params, e.g. ['state' => 'published']
	 * @return ArrayList
	 */
	public function getJobs($params = []) {
		$list = ArrayList::create();
		$response = $this->callRestfulService('jobs', $params);

		if($response && isset($response['jobs']) && is_array($response['jobs'])) {			
			foreach($response['jobs'] as $record) {
				$list->push(Workable_Result::create($record));
			}
		}

		return $list;
	}

	/**
	 * Wrapper method to configure the RestfulService, make the call, and handle errors
	 * @param  string $url    
	 * @param  array  $params 
	 * @param  string $method 
	 * @return array         JSON
	 */
	protected function callRestfulService($url, $params = [], $method = 'GET') {
		$this->restfulService->setQueryString($params);
		$response = $this->restfulService->request($url, $method, $params);
		
		if(!$response) {
			SS_Log::log('No response from workable API endpoint ' . $url, SS_Log::WARN);
			
			return false;				
		}
		else if($response->getStatusCode() !== 200) {
			SS_Log::log("Received non-200 status code {$response->getStatusCode()} from workable API", SS_Log::WARN);

			return false;
		}

		return Convert::json2array($response->getBody());
	}

}


/**
 * Defines the renderable Workable data for the template. Converts UpperCamelCase properties
 * to the snake_case that comes from the API
 */
class Workable_Result extends ViewableData {

	/**
	 * Raw data from the API
	 * @var array
	 */
	protected $apiData;

	/**
	 * Magic getter that converts SilverStripe $UpperCamelCase to snake_case
	 * e.g. $FullTitle gets full_title
	 * @param  string $prop
	 * @return mixed
	 */
	public function __get($prop) {
		$snaked = ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $prop)), '_');

		return isset($this->apiData[$snaked]) ? $this->apiData[$snaked] : null;
	}

	/**
	 * constructor
	 * @param array $apiData 
	 */
	public function __construct($apiData = []) {
		$this->apiData = $apiData;
	}
}