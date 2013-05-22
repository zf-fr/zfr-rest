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

namespace ZfrRest\Stdlib\Hydrator\Strategy;

use DoctrineModule\Stdlib\Hydrator\Strategy\AbstractCollectionStrategy;
use Zend\Stdlib\Hydrator\HydratorInterface;

/**
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class SerializationLoad extends AbstractCollectionStrategy
{
    /**
     * @var HydratorInterface
     */
    protected $associationHydrator;

    /**
     * @param HydratorInterface $associationHydrator
     */
    public function __construct(HydratorInterface $associationHydrator)
    {
        $this->associationHydrator = $associationHydrator;
    }

    /**
     * {@inheritDoc}
     */
    public function extract($value)
    {
        $result = array();

        // This need to be done better, through a strategy maybe?
        $fieldToIgnore = $this->metadata->getAssociationMappedByTargetField($this->collectionName);

        foreach ($value as $object) {
            $data = $this->associationHydrator->extract($object);
            unset($data[$fieldToIgnore]);

            $result[] = $data;
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function hydrate($value)
    {
        return $value;
    }
}
