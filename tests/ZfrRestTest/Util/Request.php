<?php
namespace ZfrRestTest\Util;

/**
 * Class Request
 * Extends \Zend\Http\Request with baseUrl capabilities.
 * Not using Zend\Http\PhpEnvironment\Request for dependencies matters.
 * @package ZfrRestTest\Util
 * @author jmleroux
 */
class Request extends \Zend\Http\Request
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
