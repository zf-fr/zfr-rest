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

/**
 * ResourceMetadataOptions
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class ResourceMetadataOptions extends AbstractOptions
{
    /**
     * FQCN of a class cache that implements Doctrine\Common\Cache\Cache
     *
     * @var string
     */
    protected $cache;

    /**
     * Drivers that are used for creating the metadata factory
     *
     * @var array
     */
    protected $drivers;


    /**
     * Set the class name of a cache that implements Doctrine\Common\Cache\Cache
     *
     * @param  string $cache
     * @throws Exception\RuntimeException
     * @return void
     */
    public function setCache($cache)
    {
        if (!is_subclass_of($cache, 'Doctrine\Common\Cache\Cache')) {
            throw new Exception\RuntimeException(sprintf(
                'Cache must implement Doctrine\Common\Cache\Cache, %s given',
                $cache
            ));
        }

        $this->cache = $cache;
    }

    /**
     * Get the class name of the cache used for metadata
     *
     * @return string
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * Set the list of drivers
     *
     * @param  array $drivers
     * @throws Exception\RuntimeException
     * @return void
     */
    public function setDrivers(array $drivers)
    {
        if (empty($drivers)) {
            throw new Exception\RuntimeException(
                'No drivers were set for the resource metadata'
            );
        }

        foreach ($drivers as $driver) {
            if (!is_subclass_of($driver['class'], 'Metadata\Driver\DriverInterface')) {
                throw new Exception\RuntimeException(sprintf(
                    'Driver class should implements Metadata\Driver\DriverInterface, %s given',
                    $driver['class']
                ));
            }
        }

        $this->drivers = $drivers;
    }

    /**
     * Get the list of drivers
     *
     * @return array
     */
    public function getDrivers()
    {
        return $this->drivers;
    }
}
