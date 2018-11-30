<?php

namespace SilverStripe\Workable;

use SilverStripe\View\ViewableData;

/**
 * Defines the renderable Workable data for the template. Converts UpperCamelCase properties
 * to the snake_case that comes from the API
 */
class WorkableResult extends ViewableData
{
    /**
     * Raw data from the API
     * @var array
     */
    protected $apiData;

    /**
     * Magic getter that converts SilverStripe $UpperCamelCase to snake_case
     * e.g. $FullTitle gets full_title. You can also use dot-separated syntax, e.g. $Location.City
     * @param  string $prop
     * @return mixed
     */
    public function __get($prop)
    {
        $snaked = ltrim(strtolower(preg_replace('/[A-Z]/', '_$0', $prop)), '_');

        if (!isset($this->apiData[$snaked])) {
            return null;
        }
        $data = $this->apiData[$snaked];

        if (is_array($this->apiData[$snaked])) {
            return new WorkableResult($data);
        }

        return $data;
    }

    /**
     * constructor
     * @param array $apiData
     */
    public function __construct($apiData = [])
    {
        $this->apiData = $apiData;
    }
}
