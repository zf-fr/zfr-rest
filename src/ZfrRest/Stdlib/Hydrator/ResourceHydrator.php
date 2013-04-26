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

namespace ZfrRest\Stdlib\Hydrator;

use Zend\EventManager\EventManagerInterface;
use Zend\Stdlib\Hydrator\AggregateHydrator;
use Zend\Stdlib\Hydrator\HydratorInterface;
use ZfrRest\Resource\Exception\RuntimeException;
use ZfrRest\Resource\Metadata\ResourceMetadataInterface;
use ZfrRest\Resource\Normalizer\ResourceNormalizerInterface;
use ZfrRest\Resource\ResourceInterface;

/**
 * @licence MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class ResourceHydrator extends AggregateHydrator
{
    /**
     * @var ResourceNormalizerInterface
     */
    protected $normalizer;

    /**
     * @param EventManagerInterface       $eventManager
     * @param HydratorInterface           $hydrator
     * @param ResourceNormalizerInterface $normalizer
     */
    public function __construct(
        EventManagerInterface $eventManager,
        HydratorInterface $hydrator,
        ResourceNormalizerInterface $normalizer
    ) {
        parent::__construct($eventManager);

        $this->attach($hydrator);
    }

    /**
     * {@inheritDoc}
     */
    public function extract($object)
    {
        if (!$object instanceof ResourceInterface) {
            throw new RuntimeException(sprintf(
                '%s can only work with object implementing ZfrRest\Resource\ResourceInterface, but %s was given',
                get_called_class(),
                get_class($object)
            ));
        }

        return $this->normalize(parent::extract($object->getData()), $object->getMetadata());
    }

    /**
     * {@inheritDoc}
     */
    public function hydrate(array $data, $object)
    {
        if (!$object instanceof ResourceInterface) {
            throw new RuntimeException(sprintf(
                '%s can only work with object implementing ZfrRest\Resource\ResourceInterface, but %s was given',
                get_called_class(),
                get_class($object)
            ));
        }

        return parent::hydrate($this->denormalize($data, $object->getMetadata()), $object->getData());
    }

    /**
     * Normalize data according to the
     * 
     * @param  array                     $data
     * @param  ResourceMetadataInterface $metadata
     * @return array
     */
    protected function normalize(array $data, ResourceMetadataInterface $metadata)
    {

    }
}
