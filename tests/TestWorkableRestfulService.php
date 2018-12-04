<?php

namespace SilverStripe\Workable\Tests;

use GuzzleHttp\Psr7\Response;

class TestWorkableRestfulService
{
    public function request($method, $url, $params = [])
    {
        switch ($url) {
            case 'jobs':
                return $this->getMockJobs();
            case 'jobs/GROOV001':
            case 'jobs/GROOV002':
                return $this->getMockJob($url);
        }
    }

    protected function getMockJobs()
    {
        return new Response(200, [], json_encode(['jobs' => [
            [
                'title' => 'Job 1',
                'shortcode' => 'GROOV001',
            ],
            [
                'title' => 'Job 2',
                'shortcode' => 'GROOV002',
            ],
        ]]));
    }

    protected function getMockJob($url)
    {
        return new Response(200, [], json_encode([
            'title' => 'Job x',
            'test' => 'full data',
            'id' => 1,
            'shortcode' => substr($url, 5),
        ]));
    }
}
