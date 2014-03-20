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

namespace ZfrRest\Router\Http\Matcher;

use Metadata\MetadataFactory;
use ZfrRest\Resource\Resource;
use ZfrRest\Resource\ResourceInterface;

/**
 * Matcher for an association sub-path
 *
 * This matcher is executed when matching an association. For instance, with the URI "/users/5/tweets", this
 * matcher will be executed for the "/tweets" sub path, the resource passed to the "matchSubPath" method
 * being the user n°5
 *
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class AssociationSubPathMatcher implements SubPathMatcherInterface
{
    /**
     * @var MetadataFactory
     */
    protected $metadataFactory;

    /**
     * @param MetadataFactory $metadataFactory
     */
    public function __construct(MetadataFactory $metadataFactory)
    {
        $this->metadataFactory = $metadataFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function matchSubPath(ResourceInterface $resource, $subPath, SubPathMatch $previousMatch = null)
    {
        // There is no need to trim $subPath again because it is done in BaseSubPathMatcher
        $pathChunks      = explode('/', $subPath);
        $associationPath = array_shift($pathChunks);

        $resourceMetadata = $resource->getMetadata();

        if (!$resourceMetadata->hasAssociationMetadata($associationPath)) {
            return null;
        }

        // User may specify a different path for a given association, however we need to retrieve the real
        // property name to be used by Doctrine, so we use the association metadata
        $associationMetadata = $resourceMetadata->getAssociationMetadata($associationPath);
        $associationName     = $associationMetadata['propertyName'];

        $classMetadata               = $resourceMetadata->getClassMetadata();
        $associationTargetClass      = $classMetadata->getAssociationTargetClass($associationName);
        $associationResourceMetadata = $this->metadataFactory->getMetadataForClass($associationTargetClass);

        $reflectionProperty = $resourceMetadata->getReflectionClass()->getProperty($associationName);
        $reflectionProperty->setAccessible(true);

        $associationData = $reflectionProperty->getValue($resource->getData());

        return new SubPathMatch(
            new Resource($associationData, $associationResourceMetadata),
            $associationPath,
            $previousMatch
        );
    }
}
