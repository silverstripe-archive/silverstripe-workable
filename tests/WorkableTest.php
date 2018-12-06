<?php

namespace SilverStripe\Workable\Tests;

use Psr\Log\LoggerInterface;
use SilverStripe\Core\Environment;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Workable\Workable;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Workable\WorkableResult;
use SilverStripe\Workable\Tests\TestWorkableRestfulService;

class WorkableTest extends SapphireTest
{
    public function setUp()
    {
        parent::setUp();
        $config = Config::inst()->get(Injector::class, 'WorkableRestfulService');
        $config['class'] = TestWorkableRestfulService::class;
        Config::inst()->update(Injector::class, 'WorkableRestfulService', $config);

        Environment::setEnv('WORKABLE_API_KEY', 'test');
        Config::inst()->update(Workable::class, 'subdomain', 'example');
    }

    public function testThrowsIfNoSubdomain()
    {
        Config::inst()->remove(Workable::class, 'subdomain');
        $this->setExpectedException('RuntimeException');

        Workable::create()->callRestfulService('test');
    }

    public function testThrowsIfNoApiKey()
    {
        Environment::setEnv('WORKABLE_API_KEY', null);
        $this->setExpectedException('RuntimeException');

        Workable::create()->callRestfulService('test');
    }

    public function testConvertsSnakeCase()
    {
        $data = WorkableResult::create(['snake_case' => 'foo']);

        $this->assertEquals('foo', $data->SnakeCase);
    }

    public function testAcceptsDotSyntax()
    {
        $data = WorkableResult::create(['snake_case' => ['nested_property' => 'foo']]);
        $result = $data->SnakeCase;
        $this->assertInstanceOf(WorkableResult::class, $result);
        $this->assertEquals('foo', $result->NestedProperty);
    }

    public function testGetJobs()
    {
        $data = Workable::create()->getJobs();

        $this->assertCount(2, $data);
        $this->assertEquals('Job 1', $data[0]->title);
        $this->assertEquals('Job 2', $data[1]->title);
    }

    public function testGetJobsWithDraftState()
    {
        $data = Workable::create()->getJobs(['state' => 'draft']);

        $this->assertCount(1, $data);
    }

    public function testGetJob()
    {
        $data = Workable::create()->getJob('GROOV001');

        $this->assertNotNull($data);
        $this->assertEquals('Job x', $data->title);
        $this->assertEquals('GROOV001', $data->shortcode);
    }

    public function testGetJobWithDraftState()
    {
        $data = Workable::create()->getJob('GROOV001', ['state' => 'draft']);

        $this->assertNotNull($data);
        $this->assertEquals('Draft Job x', $data->title);
        $this->assertEquals('GROOV001', $data->shortcode);
    }

    public function testFullJobs()
    {
        $data = Workable::create()->getFullJobs();

        $this->assertCount(2, $data);
        $this->assertEquals('full data', $data[0]->test);
        $this->assertEquals('GROOV001', $data[0]->shortcode);
        $this->assertEquals('full data', $data[1]->test);
        $this->assertEquals('GROOV002', $data[1]->shortcode);
    }

    public function testFullJobsWithDraftState()
    {
        $data = Workable::create()->getFullJobs(['state' => 'draft']);

        $this->assertCount(1, $data);
        $this->assertEquals('Draft Job x', $data[0]->title);
        $this->assertEquals('full draft data', $data[0]->test);
        $this->assertEquals('GROOV001', $data[0]->shortcode);
    }
}
