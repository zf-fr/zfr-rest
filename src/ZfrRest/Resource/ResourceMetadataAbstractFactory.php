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

namespace ZfrRest\Resource;

use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Zend\ServiceManager\AbstractFactoryInterface;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfrRest\Exception\InvalidResourceException;

/**
 * {@inheritDoc}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class ResourceMetadataAbstractFactory implements AbstractFactoryInterface
{
    /**
     * @var \Doctrine\Common\Persistence\Mapping\ClassMetadataFactory
     */
    protected $classMetadataFactory;

    /**
     * @var array
     */
    protected $resourceConfiguration;

    /**
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadataFactory $classMetadataFactory
     * @param array                                                     $resourceConfiguration
     */
    public function __construct(ClassMetadataFactory $classMetadataFactory, array $resourceConfiguration)
    {
        $this->classMetadataFactory  = $classMetadataFactory;
        $this->resourceConfiguration = $resourceConfiguration;
    }

    /**
     * {@inheritDoc}
     */
    public function canCreateServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        return isset($this->resourceConfiguration[$requestedName])
            && ! $this->classMetadataFactory->isTransient($requestedName);
    }

    /**
     * {@inheritDoc}
     *
     * @return \ZfrRest\Resource\ResourceMetadata
     *
     * @throws
     */
    public function createServiceWithName(ServiceLocatorInterface $serviceLocator, $name, $requestedName)
    {
        if ( ! $this->canCreateServiceWithName($serviceLocator, $name, $requestedName)) {
            throw new ServiceNotFoundException();
        }

        $metadata = $this->classMetadataFactory->getMetadataFor($requestedName);
        $config   = $this->resourceConfiguration[$requestedName];

        $resourceMetadata = new ResourceMetadata($metadata);

        isset($config['controller']) && $resourceMetadata->setControllerName($config['controller']);
        isset($config['input_filter']) && $resourceMetadata->setInputFilterName($config['input_filter']);
        isset($config['hydrator']) && $resourceMetadata->setHydratorName($config['hydrator']);
        isset($config['encoders']) && $resourceMetadata->setEncoderNames($config['encoders']);
        isset($config['decoders']) && $resourceMetadata->setDecoderNames($config['decoders']);
        isset($config['associations']) && $resourceMetadata->setAssociations($config['associations']);

        return $resourceMetadata;
    }
}
