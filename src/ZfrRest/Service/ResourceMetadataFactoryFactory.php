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

namespace ZfrRest\Service;

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Metadata\Cache\DoctrineCacheAdapter;
use Metadata\Driver\DriverChain;
use Metadata\Driver\FileLocator;
use Metadata\MetadataFactory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfrRest\Options\ResourceMetadataOptions;

/**
 * ResourceMetadataFactoryFactory
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class ResourceMetadataFactoryFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $options         = $serviceLocator->get('Config');
        $metadataOptions = new ResourceMetadataOptions($options['zfr_rest']['resource_metadata']);

        // Create the driver chain
        $drivers = $metadataOptions->getDrivers();
        foreach ($drivers as &$driver) {
            $class = $driver['class'];
            $paths = $driver['paths'];

            if ($class === 'ZfrRest\Resource\Metadata\Driver\PhpDriver') {
                // Special care is taken for PhpDriver, as we need to create a FileLocator
                $fileLocator = new FileLocator($paths);
                $driver      = new $class($fileLocator);
            } elseif ($class === 'ZfrRest\Resource\Metadata\Driver\AnnotationDriver') {
                // Add the path to the annotations
                AnnotationRegistry::registerAutoloadNamespace('ZfrRest\Resource\Annotation');
                $driver = new $class(new AnnotationReader());
            }
        }

        $metadataFactory = new MetadataFactory(new DriverChain($drivers));

        // Set the cache if defined
        $cache = $metadataOptions->getCache();
        if ($cache !== null) {
            $cacheAdapter = new DoctrineCacheAdapter('resource_metadata', new $cache);
            $metadataFactory->setCache($cacheAdapter);
        }

        return $metadataFactory;
    }
}
