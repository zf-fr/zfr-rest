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

namespace ZfrRest\Factory;

use DoctrineModule\Cache\ZendStorageCache;
use Metadata\Cache\DoctrineCacheAdapter;
use Zend\Cache\Storage\StorageInterface;
use Zend\Cache\StorageFactory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * Factory to create a Metadata cache compliant, from a string OR a Zend cache compliant config
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class CacheFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var \ZfrRest\Options\ModuleOptions $options */
        $options      = $serviceLocator->get('ZfrRest\Options\ModuleOptions');
        $cacheOptions = $options->getCache();

        if (is_string($cacheOptions)) {
            $cache = $serviceLocator->get($cacheOptions);
        } elseif (is_array($cacheOptions)) {
            $cache = StorageFactory::factory($cacheOptions);
        } else {
            $cache = StorageFactory::factory(['adapter' => 'memory']);
        }

        // If we have a Zend cache, we wrap it around a ZendStorageCache adapter
        if ($cache instanceof StorageInterface) {
            $cache = new ZendStorageCache($cache);
        }

        // Finally, we just wrap it around JmsMetadata cache adapter for consumption
        return new DoctrineCacheAdapter('zfr_rest_cache', $cache);
    }
}
