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

use Doctrine\Common\Collections\Selectable as SelectableInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;

/**
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class ObjectManagerResourceLoaderManager implements ResourceLoaderManagerInterface
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * {@inheritDoc}
     */
    public function getResourceLoader($resourceName, $resource)
    {
        if (!$resource instanceof SelectableInterface || !$resource instanceof ObjectRepository) {
            throw new \BadMethodCallException(
                sprintf(
                    'Not yet supported: resource must be a selectable, "%s" given',
                    is_object($resource) ? get_class($resource) : gettype($resource)
                )
            );
        }

        return new RepositoryResourceLoader($resource);
    }

    /**
     * {@inheritDoc}
     */
    public function getResourceAssociationLoader($resourceName, $associationName, $resource)
    {
        $metadata = $this->objectManager->getClassMetadata($resourceName);
        $name = $metadata->getName();

        if (!$resource instanceof $name) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Provided resource must be an instance of "%s", "%s" provided',
                    $name,
                    is_object($resource) ? get_class($resource) : gettype($resource)
                )
            );
        }

        if (!$metadata->hasAssociation($associationName)) {
            throw new Exception\UnexpectedValueException(
                sprintf('Association "%s" does not exist on resource "%s"', $associationName, $resourceName)
            );
        }

        if ($metadata->isSingleValuedAssociation($associationName)) {
            return new SingleValuedAssociationResourceLoader($resource, $associationName, $metadata);
        }

        if ($metadata->isCollectionValuedAssociation($associationName)) {
            return new CollectionValuedAssociationResourceLoader($resource, $associationName, $metadata);
        }

        throw new \InvalidArgumentException(
            sprintf('Association "%s" on resource "%s" is neither single nor collection valued?!')
        );
    }
}
