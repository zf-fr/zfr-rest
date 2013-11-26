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

namespace ZfrRest\Resource\Metadata;

use Doctrine\Common\Persistence\Mapping\ClassMetadata as DoctrineClassMetadata;
use Metadata\MergeableClassMetadata;
use ZfrRest\Resource\Exception;
use ZfrRest\Resource\Resource;

/**
 * ResourceMetadata
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class ResourceMetadata extends MergeableClassMetadata implements ResourceMetadataInterface
{
    /**
     * {@inheritDoc}
     */
    public function createResource()
    {
        $args = func_get_args();

        if (empty($args)) {
            return new Resource($this->reflection->newInstance(), $this);
        }

        return new Resource($this->reflection->newInstanceArgs($args), $this);
    }

    /**
     * {@inheritDoc}
     */
    public function getClassMetadata()
    {
        return $this->propertyMetadata['classMetadata'];
    }

    /**
     * {@inheritDoc}
     */
    public function getControllerName()
    {
        return $this->propertyMetadata['controller'];
    }

    /**
     * {@inheritDoc}
     */
    public function getInputFilterName()
    {
        return $this->propertyMetadata['inputFilter'];
    }

    /**
     * {@inheritDoc}
     */
    public function getHydratorName()
    {
        return $this->propertyMetadata['hydrator'];
    }

    /**
     * {@inheritDoc}
     */
    public function getCollectionMetadata()
    {
        return $this->propertyMetadata['collectionMetadata'];
    }

    /**
     * {@inheritDoc}
     */
    public function hasAssociation($association)
    {
        return isset($this->propertyMetadata['associations'][$association]);
    }

    /**
     * {@inheritDoc}
     */
    public function getAssociationMetadata($association)
    {
        return $this->propertyMetadata['associations'][$association];
    }
}
