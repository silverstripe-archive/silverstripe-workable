<?php

class TestWorkableRestfulService extends RestfulService {

	public $params = [];

	public function setQueryString($params = NULL) {
		$this->params = $params;
	}


	public function request($subURL = '', $method = "GET", $data = null, $headers = null, $curlOptions = array()) {
		switch($subURL) {
			case 'jobs':
				if($this->params['state'] === 'published') {
					return new RestfulService_Response(
						json_encode(['jobs' => [
							['title' => 'Published Job 1'],
							['title' => 'Published Job 2'],
							['title' => 'Published Job 3']
						]]),
						200
					);
				}
				if($this->params['state'] === 'draft') {
					return new RestfulService_Response(
						json_encode(['jobs' => [
							['title' => 'Draft Job 1']
						]]),
						200
					);					
				}
				if($this->params['state'] === 'fail') {
					return new RestfulService_Response('FAIL', 404);
				}
			break;
		}

	}

}
