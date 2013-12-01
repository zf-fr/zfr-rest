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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Traversable;
use ZfrRest\Router\Exception\RuntimeException;
use ZfrRest\Resource\Resource;
use ZfrRest\Resource\ResourceInterface;

/**
 * Matcher for an collection sub-path
 *
 * This matcher is executed when matching an item of a collection. For instance, with the URI "/users/5", this
 * matcher will be executed for the "/5" sub path, the resource passed to the "matchSubPath" method
 * being the users collection
 *
 * This matcher can also be executed at the end of a path (for instance "/users"). In this case, it
 * will trigger an event to allow filtering the collection
 *
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class CollectionSubPathMatcher implements SubPathMatcherInterface
{
    /**
     * {@inheritDoc}
     */
    public function matchSubPath(ResourceInterface $resource, $subPath, SubPathMatch $previousMatch = null)
    {
        $pathChunks = explode('/', $subPath);
        $identifier = array_shift($pathChunks);

        $data = $this->findItem($resource, $identifier);

        if (null === $data) {
            return null;
        }

        // If we have /users/5, metadata for both /users and /5 parts is the same
        return new SubPathMatch(
            new Resource($data, $resource->getMetadata()),
            $identifier,
            $previousMatch
        );
    }

    /**
     * Retrieves a single item in the collection by its identifier
     *
     * @param  ResourceInterface $resource
     * @param  string            $identifier
     * @return mixed|null
     * @throws RuntimeException on composite identifiers (not yet supported)
     */
    protected function findItem(ResourceInterface $resource, $identifier)
    {
        $classMetadata   = $resource->getMetadata()->getClassMetadata();
        $identifierNames = $classMetadata->getIdentifierFieldNames();

        if (count($identifierNames) > 1) {
            throw new RuntimeException(get_class($this) . ' does not support composite identifiers');
        }

        $data = $resource->getData();

        if (!$data instanceof Selectable && $data instanceof Traversable) {
            $data = new ArrayCollection(iterator_to_array($data));
        }

        $criteria = new Criteria();
        $criteria->andWhere($criteria->expr()->eq(reset($identifierNames), $identifier));

        $found = $data->matching($criteria);

        return $found->isEmpty() ? null : $found->first();
    }
}
