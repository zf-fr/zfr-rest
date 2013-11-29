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
use ZfrRest\Exception\RuntimeException;
use ZfrRest\Resource\Metadata\Driver\AnnotationDriver;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class ResourceMetadataFactoryFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        /* @var $moduleOptions \ZfrRest\Options\ModuleOptions $moduleOptions */
        $moduleOptions = $serviceLocator->get('ZfrRest\Options\ModuleOptions');

        // @TODO: how to handle data coming from multiple object managers?

        /* @var \Doctrine\Common\Persistence\ObjectManager $objectManager */
        $objectManager           = $serviceLocator->get($moduleOptions->getObjectManager());
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
                        $doctrineMetadataFactory
                    );
                    break;
                default:
                    throw new RuntimeException(sprintf(
                        'Unrecognized driver class "%s" given',
                        $driverOptions->getClass()
                    ));
            }

            $driverChain->addDriver($driver);
        }

        $resourceMetadataFactory->setCache($serviceLocator->get('ZfrRest\Cache'));

        return $resourceMetadataFactory;
    }
}
