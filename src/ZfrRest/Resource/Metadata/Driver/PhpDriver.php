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
use Metadata\Driver\AbstractFileDriver;
use Metadata\Driver\FileLocatorInterface;
use ZfrRest\Resource\Metadata\ResourceAssociationMetadata;
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

        // If config has any associations set, handle it first
        if (isset($config['associations'])) {
            foreach ($config['associations'] as $associationName => $values) {
                $targetClass                 = $classMetadata->getAssociationTargetClass($associationName);
                $resourceAssociationMetadata = new ResourceAssociationMetadata($targetClass);

                foreach ($values as $key => $value) {
                    $this->processMetadata($resourceAssociationMetadata, $key, $value);
                }

                $resourceMetadata->propertyMetadata['associations_metadata'][$targetClass] = $resourceAssociationMetadata;
            }

            unset($config['associations']);
        }

        // Then handle class values
        foreach ($config as $key => $value) {
            $this->processMetadata($resourceMetadata, $key, $value);
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function getExtension()
    {
        return 'php';
    }

    /**
     * @param  \Metadata\ClassMetadata $metadata
     * @param  string                  $key
     * @param  mixed                   $value
     * @return void
     */
    private function processMetadata(ClassMetadata $metadata, $key, $value)
    {
        switch($key) {
            case 'controller':
                $metadata->propertyMetadata['controller'] = $value;
                break;
            case 'hydrator':
                $metadata->propertyMetadata['hydrator'] = $value;
                break;
            case 'input_filter':
                $metadata->propertyMetadata['input_filter'] = $value;
                break;
            case 'decoders':
                foreach ($value as $mimeType => $decoder) {
                    $metadata->propertyMetadata['decoders'][] = array(
                        $mimeType => $decoder
                    );
                }

                break;
            case 'encoders':
                foreach ($value as $mimeType => $encoder) {
                    $metadata->propertyMetadata['encoders'][] = array(
                        $mimeType => $encoder
                    );
                }

                break;
            default:
                // Ignore unknown annotations
                break;
        }
    }
}
