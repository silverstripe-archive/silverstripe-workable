<?php

class WorkableTest extends SapphireTest {

	public function setUp() {
		$config = Config::inst()->get('Injector','WorkableRestfulService');
		$config['class'] = 'TestWorkableRestfulService';
		Config::inst()->update('Injector','WorkableRestfulService', $config);

		Config::inst()->update('Workable', 'apiKey', 'test');
		Config::inst()->update('Workable', 'subdomain', 'example');
	}

	// public function testThrowsIfNoAPIKey () {
	// 	Config::inst()->remove('Workable','apiKey');
	// 	$this->setExpectedException('RuntimeException');

	// 	Workable::create();
	// }

	public function testThrowsIfNoSubdomain () {
		Config::inst()->remove('Workable','subdomain');
		$this->setExpectedException('RuntimeException');

		Workable::create();
	}

	public function testWillUseAPIKeyConstant () {
		Config::inst()->remove('Workable','apiKey');
		if(!defined('WORKABLE_API_KEY')) {
			define('WORKABLE_API_KEY','test');	
		}
		
		Workable::create();
	}

	public function testGetsPublishedJobs () {
		$result = Workable::create()->getJobs(['state' => 'published']);

		$this->assertEquals($result->count(), 3);
		$this->assertEquals($result->first()->Title, 'Published Job 1');
	}

	public function testGetsUnpublishedJobs () {
		$result = Workable::create()->getJobs(['state' => 'draft']);

		$this->assertEquals($result->count(), 1);
		$this->assertEquals($result->first()->Title, 'Draft Job 1');
	}

	public function testLogsError () {
		$logger = new TestWorkableLogger();
		SS_Log::add_writer($logger);
		$result = Workable::create()->getJobs(['state' => 'fail']);

		$this->assertNotNull($logger->event);
	}

	public function testConvertsSnakeCase () {
		$data = new Workable_Result(['snake_case' => 'foo']);

		$this->assertEquals($data->SnakeCase, 'foo');
	}

	public function testAcceptsDotSyntax () {
		$data = new Workable_Result(['snake_case' => ['nested_property' => 'foo']]);
		$result = $data->SnakeCase;
		$this->assertInstanceOf('Workable_Result', $result);
		$this->assertEquals($result->NestedProperty, 'foo');
	}
}