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

use Metadata\MetadataFactory;

/**
 * This factory allows to lazy-load metadata for association and collection as well
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 *
 * @method ResourceMetadataInterface getMetadataForClass($string)
 */
class ResourceMetadataFactory extends MetadataFactory
{
    /**
     * Load metadata for association by applying merging logic
     *
     * ZfrRest allows users to specify metadata on a given entity and to override all or part of this
     * mapping at the association level. This method handle this logic
     *
     * @param  string|ResourceMetadataInterface $class
     * @param  string                           $association
     * @return ResourceMetadataInterface
     */
    public function getAssociationMetadataForClass($class, $association)
    {
        $classMetadata = $class instanceof ResourceMetadataInterface ? $class : $this->getMetadataForClass($class);

        $associationTargetClass = $classMetadata->getClassMetadata()->getAssociationTargetClass($association);
        $associationMetadata    = clone $this->getMetadataForClass($associationTargetClass);

        // We need to merge both resource metadata AND collection resource metadata
        $baseAssociationMetadata = $classMetadata->getAssociationMetadata($association);

        $collectionMetadata = $associationMetadata->propertyMetadata['collectionMetadata'];
        $collectionMetadata->merge($baseAssociationMetadata->propertyMetadata['collectionMetadata']);

        $associationMetadata->merge($baseAssociationMetadata);

        $associationMetadata->propertyMetadata['collectionMetadata'] = $collectionMetadata;

        return $associationMetadata;
    }
}
