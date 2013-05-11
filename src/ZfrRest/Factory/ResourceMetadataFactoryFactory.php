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

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Metadata\Cache\CacheInterface;
use Metadata\Driver\DriverChain;
use Metadata\Driver\FileLocator;
use Metadata\MetadataFactory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfrRest\Factory\Exception\RuntimeException;
use ZfrRest\Resource\Metadata\Driver\PhpDriver;
use ZfrRest\Resource\Metadata\Driver\ResourceMetadataDriverInterface;

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
        /** @var $moduleOptions \ZfrRest\Options\ModuleOptions */
        $moduleOptions   = $serviceLocator->get('ZfrRest\Options\ModuleOptions');
        $resourceOptions = $moduleOptions->getResourceMetadata();

        // TODO: change that
        $objectManager = 'doctrine.entitymanager.orm_default';
        if (!$serviceLocator->has($objectManager)) {
            throw new RuntimeException(sprintf(
                'The object manager key is not valid, %s given',
                $objectManager
            ));
        }

        $doctrineMetadataFactory = $serviceLocator->get($objectManager)->getMetadataFactory();
        $driversOptions          = $resourceOptions->getDrivers();
        $metadataDrivers         = array();

        // The annotation driver does not need to be added twice
        foreach ($driversOptions as $driverOptions) {
            $class = $driverOptions['class'];

            if ($class === 'ZfrRest\Resource\Metadata\Driver\AnnotationDriver') {
                // Add the path to the annotations
                AnnotationRegistry::registerAutoloadNamespace(
                    'ZfrRest\Resource\Annotation',
                    __DIR__ . '/../..'
                );

                $metadataDrivers[] = new $class(new AnnotationReader(), $doctrineMetadataFactory);

                break;
            }
        }


        // We need to handle both drivers differently, as the FileDriver needs all the paths to work properly
        $filePaths = array();

        foreach ($driversOptions as $driverOptions) {
            $class = $driverOptions['class'];

            if ($class === 'ZfrRest\Resource\Metadata\Driver\PhpDriver') {
                $filePaths = array_merge($filePaths, $driverOptions['paths']);
            }
        }

        if (!empty($filePaths)) {
            $metadataDrivers[] = new PhpDriver(new FileLocator($filePaths), $doctrineMetadataFactory);
        }

        $resourceMetadataFactory = new MetadataFactory(new DriverChain($metadataDrivers));

        // We need to inject the resource metadata factory into each driver to allow them to retrieve
        // metadata from one driver to another
        foreach ($metadataDrivers as $metadataDriver) {
            if ($metadataDriver instanceof ResourceMetadataDriverInterface) {
                $metadataDriver->setResourceMetadataFactory($resourceMetadataFactory);
            }
        }

        // Also add a cache if one is set
        $cache = $serviceLocator->get('ZfrRest\Resource\Metadata\CacheProvider');
        
        if ($cache instanceof CacheInterface) {
            $resourceMetadataFactory->setCache($cache);
        }

        return $resourceMetadataFactory;
    }
}
