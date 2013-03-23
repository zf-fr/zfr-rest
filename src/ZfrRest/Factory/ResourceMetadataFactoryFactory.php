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
use Metadata\Driver\DriverChain;
use Metadata\Driver\FileLocator;
use Metadata\MetadataFactory;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfrRest\Factory\Exception\RuntimeException;

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

        $objectManager = $resourceOptions->getObjectManager();
        if (!$serviceLocator->has($objectManager)) {
            throw new RuntimeException(sprintf(
                'The object manager key is not valid, %s given',
                $objectManager
            ));
        }

        $objectManager = $serviceLocator->get($objectManager);
        $drivers       = $resourceOptions->getDrivers();

        foreach ($drivers as &$driver) {
            $class = $driver['class'];
            $paths = $driver['paths'];

            if ($class === 'ZfrRest\Resource\Metadata\Driver\PhpDriver') {
                // Special care is taken for PhpDriver, as we need to create a FileLocator
                $fileLocator = new FileLocator($paths);
                $driver      = new $class($fileLocator, $objectManager);
            } elseif ($class === 'ZfrRest\Resource\Metadata\Driver\AnnotationDriver') {
                // Add the path to the annotations
                AnnotationRegistry::registerAutoloadNamespace(
                    'ZfrRest\Resource\Annotation',
                    __DIR__ . '/../..'
                );

                $driver = new $class(new AnnotationReader(), $objectManager);
            }
        }

        $metadataFactory = new MetadataFactory(new DriverChain($drivers));

        return $metadataFactory;
    }
}
