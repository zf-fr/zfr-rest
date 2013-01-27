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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use ZfrRest\Resource\Exception\UnexpectedValueException;

/**
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class CollectionValuedAssociationResourceExtractor implements ResourceExtractorInterface
{
    /**
     * @var mixed
     */
    protected $resource;

    /**
     * @var string
     */
    protected $association;

    /**
     * @var \Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    protected $metadata;

    /**
     * @param mixed                                              $resource
     * @param string                                             $association
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $metadata
     *
     * @throws \ZfrRest\Resource\Exception\UnexpectedValueException
     */
    public function __construct($resource, $association, ClassMetadata $metadata)
    {
        if (!is_a($resource, $metadata->getName())) {
            throw UnexpectedValueException::invalidResourceException($resource, $metadata);
        }

        $this->resource    = $resource;
        $this->association = $association;
        $this->metadata    = $metadata;
    }

    /**
     * {@inheritDoc}
     */
    function matching(Criteria $criteria)
    {
        $reflectionProperty = $this->metadata->getReflectionClass()->getProperty($this->association);
        $reflectionProperty->setAccessible(true);
        $value = $reflectionProperty->getValue($this->resource);

        if (!$value instanceof Selectable && $criteria->getWhereExpression()) {
            throw UnexpectedValueException::nonMatchableCollection($value);
        }

        if ($value instanceof Selectable) {
            return $value->matching($criteria);
        }

        if (is_array($value)) {
            return new ArrayCollection($value);
        }

        throw UnexpectedValueException::unknownCollectionType($value);
    }
}
