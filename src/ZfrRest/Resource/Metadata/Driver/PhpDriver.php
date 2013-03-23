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

namespace ZfrRest\Resource\Metadata\Driver;

use ReflectionClass;
use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Metadata\ClassMetadata;
use Metadata\PropertyMetadata;
use Metadata\Driver\AbstractFileDriver;
use Metadata\Driver\FileLocatorInterface;
use Zend\Filter\StaticFilter;
use ZfrRest\Resource\Metadata\ResourceMetadata;

/**
 * PhpDriver
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class PhpDriver extends AbstractFileDriver
{
    /**
     * @var ClassMetadataFactory
     */
    protected $classMetadataFactory;


    /**
     * @param \Metadata\Driver\FileLocatorInterface                     $locator
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadataFactory $classMetadataFactory
     */
    public function __construct(FileLocatorInterface $locator, ClassMetadataFactory $classMetadataFactory)
    {
        parent::__construct($locator);
        $this->classMetadataFactory = $classMetadataFactory;
    }


    /**
     * {@inheritDoc}
     */
    protected function loadMetadataFromFile(ReflectionClass $class, $file)
    {
        $config = include $file;

        $classMetadata    = $this->classMetadataFactory->getMetadataFor($class->getName());

        $resourceMetadata = new ResourceMetadata($class->getName());
        $resourceMetadata->classMetadata = $classMetadata;

        // First process associations, so that we can safely remove it and handle the other config normally
        if (isset($config['associations'])) {
            foreach ($config['associations'] as $associationName => $associationConfig) {
                $targetClass                 = $classMetadata->getAssociationTargetClass($associationName);
                $resourceAssociationMetadata = new ResourceMetadata($targetClass);

                $this->processMetadata($resourceAssociationMetadata, $associationConfig);
                $resourceMetadata->associations[$associationName] = $resourceAssociationMetadata;
            }

            unset($config['associations']);
        }

        $this->processMetadata($resourceMetadata, $config);

        return $resourceMetadata;
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtension()
    {
        return 'php';
    }

    /**
     * @param \Metadata\ClassMetadata $metadata
     * @param array                   $data
     */
    private function processMetadata(ClassMetadata $metadata, array $data)
    {
        foreach ($data as $key => $value) {
            // Normalize the key (in a PHP array, the keys are underscore_separated)
            $key = lcfirst(StaticFilter::execute($key, 'WordUnderscoreToCamelCase'));

            $propertyMetadata = new PropertyMetadata($metadata, $key);
            $propertyMetadata->setValue($metadata, $value);
        }
    }
}
