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
use Metadata\Driver\DriverChain;
use Metadata\MetadataFactory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfrRest\Factory\Exception;
use ZfrRest\Resource\Metadata\Driver\AnnotationDriver;

/**
 * Factory used to create the resource metadata factory
 *
 * @author Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class ResourceMetadataFactoryFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /** @var $moduleOptions \ZfrRest\Options\ModuleOptions $moduleOptions */
        $moduleOptions = $serviceLocator->get('ZfrRest\Options\ModuleOptions');

        $objectManager = $moduleOptions->getObjectManager();
        if (!$serviceLocator->has($objectManager)) {
            throw Exception\RuntimeException::missingObjectManager($objectManager);
        }

        /** @var \Doctrine\Common\Persistence\ObjectManager $objectManager */
        $objectManager           = $serviceLocator->get($objectManager);
        $doctrineMetadataFactory = $objectManager->getMetadataFactory();

        $driverChain             = new DriverChain();
        $resourceMetadataFactory = new MetadataFactory($driverChain);

        $drivers = $moduleOptions->getDrivers();
        foreach ($drivers as $driverOptions) {
            $driver = null;

            switch($driverOptions->getClass()) {
                case 'ZfrRest\Resource\Metadata\Driver\AnnotationDriver':
                    $driver = new AnnotationDriver(
                        new AnnotationReader(),
                        $resourceMetadataFactory,
                        $doctrineMetadataFactory
                    );
                    break;
                default:
                    throw Exception\RuntimeException::invalidDriverClass($driverOptions->getClass());
            }

            $driverChain->addDriver($driver);
        }

        if ($moduleOptions->getCache()) {
            $resourceMetadataFactory->setCache($serviceLocator->get($moduleOptions->getCache()));
        }

        return $resourceMetadataFactory;
    }
}
