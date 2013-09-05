<?php
namespace ZfrRestTest\Asset\Request;

use Zend\Http\Request as BaseRequest;

/**
 * Extends \Zend\Http\Request with baseUrl capabilities.
 * Not using Zend\Http\PhpEnvironment\Request for dependencies matters.
 * @package ZfrRestTest\Asset
 * @author jmleroux
 */
class Request extends BaseRequest
{
    /**
     * @var string
     */
    protected $baseUrl;

    /**
     * @param $baseUrl
     * @return $this
     */
    public function setBaseUrl($baseUrl)
    {
        $this->baseUrl = $baseUrl;
        return $this;
    }

    /**
     * @return string
     */
    public function getBaseUrl()
    {
        return $this->baseUrl;
    }
}
