<?php

namespace SilverStripe\Workable;

use Psr\Log\LoggerInterface;
use SilverStripe\Core\Environment;
use SilverStripe\Dev\SapphireTest;
use SilverStripe\Workable\Workable;
use SilverStripe\Core\Config\Config;
use SilverStripe\Core\Injector\Injector;
use SilverStripe\Workable\WorkableResult;
use SilverStripe\Workable\TestWorkableRestfulService;

class WorkableTest extends SapphireTest
{
    public function setUp()
    {
        parent::setUp();

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
}
