<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace ZfrRest\Options;

use Zend\Stdlib\AbstractOptions;
use ZfrRest\Exception\InvalidArgumentException;

/**
 * @author Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class ModuleOptions extends AbstractOptions
{
    /**
     * Key of the object manager fetched from service locator
     *
     * @var string|null
     */
    protected $objectManager;

    /**
     * Options for all drivers
     *
     * @var DriverOptions[]
     */
    protected $drivers = [];

    /**
     * Key of the cache or a Zend\Cache compliant config
     *
     * @var string|array|null
     */
    protected $cache;

    /**
     * Array that map a custom exception to a HttpExceptionInterface exception
     *
     * @var array
     */
    protected $exceptionMap = [];

    /**
     * Should we register this listener?
     *
     * @var bool
     */
    protected $registerHttpMethodOverrideListener = false;

    /**
     * Is the enable coalesce filtering enabled?
     *
     * If enabled, it allows the REST router to filter a collection list by identifiers. For instance, considering
     * a query /customers?$ids[]=1&$ids[]=2, it will be able to return a filtered collections
     *
     * @var bool
     */
    protected $enableCoalesceFiltering = false;

    /**
     * The coalesce filtering query key
     *
     * @var string
     */
    protected $coalesceFilteringQueryKey = '$ids';

    /**
     * @param array|null $options
     */
    public function __construct($options = null)
    {
        $this->__strictMode__ = false;
        parent::__construct($options);
    }

    /**
     * Set the object manager key
     *
     * @param  string $objectManager
     * @return void
     */
    public function setObjectManager($objectManager)
    {
        $this->objectManager = (string) $objectManager;
    }

    /**
     * Get the object manager key
     *
     * @return string
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * @param  array $drivers
     * @return void
     */
    public function setDrivers(array $drivers)
    {
        foreach ($drivers as $driverOptions) {
            $this->drivers[] = new DriverOptions($driverOptions);
        }
    }

    /**
     * @return DriverOptions[]
     */
    public function getDrivers()
    {
        return $this->drivers;
    }

    /**
     * @param  string|array $cache
     * @return void
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return string
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param  array $exceptionMap
     * @return void
     */
    public function setExceptionMap(array $exceptionMap)
    {
        $this->exceptionMap = $exceptionMap;
    }

    /**
     * @return array
     */
    public function getExceptionMap()
    {
        return $this->exceptionMap;
    }

    /**
     * @param  boolean $registerHttpMethodOverrideListener
     * @return void
     */
    public function setRegisterHttpMethodOverrideListener($registerHttpMethodOverrideListener)
    {
        $this->registerHttpMethodOverrideListener = (bool) $registerHttpMethodOverrideListener;
    }

    /**
     * @return boolean
     */
    public function getRegisterHttpMethodOverrideListener()
    {
        return $this->registerHttpMethodOverrideListener;
    }

    /**
     * @param boolean $enableCoalesceFiltering
     */
    public function setEnableCoalesceFiltering($enableCoalesceFiltering)
    {
        $this->enableCoalesceFiltering = (bool) $enableCoalesceFiltering;
    }

    /**
     * @return boolean
     */
    public function isEnableCoalesceFiltering()
    {
        return $this->enableCoalesceFiltering;
    }

    /**
     * @param  string $coalesceFilteringQueryKey
     * @throws InvalidArgumentException
     */
    public function setCoalesceFilteringQueryKey($coalesceFilteringQueryKey)
    {
        if (empty($coalesceFilteringQueryKey)) {
            throw new InvalidArgumentException('Coalesce filtering key cannot be an empty value');
        }

        $this->coalesceFilteringQueryKey = (string) $coalesceFilteringQueryKey;
    }

    /**
     * @return string
     */
    public function getCoalesceFilteringQueryKey()
    {
        return $this->coalesceFilteringQueryKey;
    }
}
