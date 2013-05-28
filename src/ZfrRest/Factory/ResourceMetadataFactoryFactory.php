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
use Doctrine\Common\Persistence\ObjectManager;
use Metadata\Cache\CacheInterface;
use Metadata\Driver\DriverChain;
use Metadata\Driver\FileLocator;
use Metadata\MetadataFactory;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfrRest\Factory\Exception\RuntimeException;
use ZfrRest\Resource\Metadata\Driver\AnnotationDriver;
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

        try {
            /* @var $objectManager \Doctrine\Common\Persistence\ObjectManager */
            $objectManager = $serviceLocator->get($moduleOptions->getObjectManager());
        } catch (ServiceNotFoundException $exception) {
            throw RuntimeException::missingObjectManager($moduleOptions->getObjectManager(), $exception);
        }

        if (! $objectManager instanceof ObjectManager) {
            throw RuntimeException::invalidObjectManager($moduleOptions->getObjectManager(), $objectManager);
        }

        $doctrineMetadataFactory = $objectManager->getMetadataFactory();
        $driversOptions          = $resourceOptions->getDrivers();
        /* @var $metadataDrivers ResourceMetadataDriverInterface[] */
        $metadataDrivers         = array();

        // The annotation driver does not need to be added twice
        foreach ($driversOptions as $driverOptions) {
            switch ($driverOptions['class']) {
                case 'ZfrRest\Resource\Metadata\Driver\AnnotationDriver':
                    //AnnotationRegistry::registerAutoloadNamespace('ZfrRest\Resource\Annotation', __DIR__ . '/../..');
                    $metadataDrivers[] = new AnnotationDriver(new AnnotationReader(), $doctrineMetadataFactory);
                    break;
                case 'ZfrRest\Resource\Metadata\Driver\PhpDriver':
                    $metadataDrivers[] = new PhpDriver(
                        new FileLocator($driverOptions['paths']),
                        $doctrineMetadataFactory
                    );
                    break;
            }
        }

        $resourceMetadataFactory = new MetadataFactory(new DriverChain($metadataDrivers));

        // We need to inject the resource metadata factory into each driver to allow them to retrieve
        // metadata from one driver to another
        // @todo this part should be removed
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
