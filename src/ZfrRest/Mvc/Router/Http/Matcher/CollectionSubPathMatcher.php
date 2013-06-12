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

namespace ZfrRest\Mvc\Router\Http\Matcher;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Traversable;
use Zend\Http\Request;
use Zend\Stdlib\ArrayUtils;
use ZfrRest\Mvc\Exception;
use ZfrRest\Mvc\Exception\RuntimeException;
use ZfrRest\Resource\Resource;
use ZfrRest\Resource\ResourceInterface;

/**
 * {@inheritDoc}
 *
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class CollectionSubPathMatcher implements SubPathMatcherInterface
{
    /**
     * {@inheritDoc}
     */
    public function matchSubPath(
        ResourceInterface $resource,
        $subPath,
        Request $request,
        SubPathMatch $previousMatch = null
    ) {
        if (! $resource->isCollection()) {
            return null;
        }

        $path = trim($subPath, '/');

        if (empty($path)) {
            return new SubPathMatch($this->filterAssociation($resource, $request), $subPath);
        }

        $pathChunks    = explode('/', trim($subPath, '/'));
        $identifier    = array_shift($pathChunks);
        $classMetadata = $resource->getMetadata()->getClassMetadata();
        $data          = $this->findItem($resource->getData(), $classMetadata->getIdentifierFieldNames(), $identifier);

        if (null === $data) {
            // @todo is a null value actually valid?
            return null;
        }

        return new SubPathMatch(
            new Resource($data, $resource->getMetadata()),
            substr($subPath, strpos($subPath, $identifier), strlen($identifier)),
            $previousMatch
        );
    }

    /**
     * Retrieves a single item in the collection by its identifier
     *
     * @param mixed $data
     * @param array $identifierNames
     * @param mixed $identifier
     *
     * @return mixed|null
     *
     * @throws \ZfrRest\Mvc\Exception\RuntimeException on composite identifiers (not yet supported)
     */
    protected function findItem($data, array $identifierNames, $identifier)
    {
        if (count($identifierNames) > 1) {
            throw new RuntimeException(get_class($this) . ' is not able to handle composite identifiers');
        }

        if (! $data instanceof Selectable) {
            if (! ($data instanceof Traversable || is_array($data))) {
                // Can only match selectable resources
                return null;
            }

            $data = new ArrayCollection(ArrayUtils::iteratorToArray($data));
        }

        $criteria = new Criteria();

        $criteria->andWhere($criteria->expr()->eq(reset($identifierNames), $identifier));

        $found = $data->matching($criteria);

        return $found->isEmpty() ? null : $found->first();
    }

    /**
     * Filters the given resource by using the request object, then return the filtered subset
     *
     * @param ResourceInterface $resource
     * @param Request $request
     *
     * @return ResourceInterface
     */
    protected function filterAssociation(ResourceInterface $resource, Request $request)
    {
        if (! $resource->isCollection()) {
            // can only filter collections
            return $resource;
        }

        $data = $resource->getMetadata();

        if (! $data instanceof Selectable) {
            return $resource;
        }

        $criteria      = new Criteria();
        $classMetadata = $resource->getMetadata()->getClassMetadata();

        // @todo do we really need this part? This filtering is not safe by default
        // @fixme to be removed before merge
        foreach ($request->getQuery() as $parameterName => $parameterValue) {
            if ($classMetadata->hasField($parameterName)) {
                $criteria->expr()->eq($parameterName, $parameterValue);
            }
        }

        return new Resource($data->matching($criteria), $resource->getMetadata());
    }
}
