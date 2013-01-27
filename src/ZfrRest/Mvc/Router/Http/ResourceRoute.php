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

namespace ZfrRest\Mvc\Router\Http;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Zend\Mvc\Router\Http\Part;
use Zend\Mvc\Router\Http\RouteInterface;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Mvc\Router\Http\Segment;
use Zend\Stdlib\RequestInterface;
use ZfrRest\Mvc\Exception;
use ZfrRest\Resource\ResourceExtractorManagerInterface;
use ZfrRest\Resource\ResourceManagerInterface;

/**
 * HttpExceptionListener
 *
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class ResourceRoute implements RouteInterface
{
    /**
     * @var string
     */
    protected $route;

    /**
     * @var \ZfrRest\Resource\ResourceManagerInterface
     */
    protected $resourceManager;

    /**
     * @var \ZfrRest\Resource\ResourceExtractorManagerInterface
     */
    protected $resourceExtractorManager;

    /**
     * @var mixed
     */
    protected $resource;

    /**
     * @var string
     */
    protected $resourceName;

    /**
     * @param \ZfrRest\Resource\ResourceManagerInterface          $resourceManager
     * @param \ZfrRest\Resource\ResourceExtractorManagerInterface $resourceExtractorManager
     * @param string                                              $route
     * @param mixed                                               $resource
     * @param string                                              $resourceName
     */
    public function __construct(
        ResourceManagerInterface $resourceManager,
        ResourceExtractorManagerInterface $resourceExtractorManager,
        $route,
        $resource,
        $resourceName
    ) {
        $this->resourceManager          = $resourceManager;
        $this->resourceExtractorManager = $resourceExtractorManager;
        $this->route                    = (string) $route;
        $this->resource                 = $resource;
        $this->resourceName             = (string) $resourceName;
    }

    /**
     * {@inheritDoc}
     */
    public function assemble(array $params = array(), array $options = array())
    {
        throw new \BadMethodCallException('Not yet implemented');
    }

    /**
     * {@inheritDoc}
     */
    public function getAssembledParams()
    {
        throw new \BadMethodCallException('Not yet implemented');
    }

    /**
     * {@inheritDoc}
     */
    public static function factory($options = array())
    {
        throw new \BadMethodCallException(sprintf(
            'Resource route should not be created from the method "%s"',
            __CLASS__
        ));
    }

    /**
     * {@inheritDoc}
     */
    public function match(RequestInterface $request, $pathOffset = null)
    {
        if (!method_exists($request, 'getUri')) {
            return null;
        }

        /* @var $request \Zend\Http\Request */
        $uri  = $request->getUri();
        $path = $uri->getPath();

        if ($path === $this->route) {
            return new RouteMatch(array('resource' => $this->resource), strlen($this->route));
        }

        if (0 !== strpos($path, $this->route)) {
            return null;
        }

        if (!$this->resource instanceof Selectable) {
            return null; // cannot filter on a resource that is not selectable
        }

        $offset = strlen($this->route);

        return $this->matchMultiValueAssociation(
            $this->resource,
            $this->resourceManager->getResourceClassMetadata($this->resourceName),
            substr($path, $offset),
            $offset
        );
    }

    /**
     * Matches an association on a single resource. If the association is not found or was not configured to be
     * matched, returns null, otherwise tries to either retrieve the value of the association or continue iterating
     * over associations if the path was not completely crawled.
     *
     * @param mixed                                              $resource
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $metadata
     * @param string                                             $path
     *
     * @return null|\Zend\Mvc\Router\Http\RouteMatch
     *
     * @todo move to external association matcher?
     */
    protected function matchAssociation($resource, ClassMetadata $metadata, $path)
    {
        $association = $this->firstPathChunk($path);

        if (
            empty($association)
            || !$this->resourceManager->hasResourceAssociation($metadata->getName(), $association)
        ) {
            return null; // association not found
        }

        $extractor = $this->resourceExtractorManager->getResourceAssociationExtractor(
            $metadata->getName(),
            $association,
            $resource
        );

        $subPath = $this->trimFirstPathChunk($path);
        $target  = $this->resourceManager->getResourceClassMetadata($metadata->getAssociationTargetClass($association));

        if ($metadata->isSingleValuedAssociation($association)) {
            return $this->matchSingleValueAssociation($extractor, $target, $subPath);
        }

        if ($this->isEmptyPath($subPath)) {
            return new RouteMatch(array('resource' => $extractor->matching(new Criteria()), strlen($this->route)));
        }

        return $this->matchMultiValueAssociation($extractor, $target, $subPath);
    }

    /**
     * Matches a collection valued association on a resource for a single value in it. If none found, returns null,
     * otherwise continues iterating over associations on the value until the path is completely crawled.
     *
     * @param \Doctrine\Common\Collections\Selectable            $resource
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $metadata
     * @param string                                             $path
     *
     * @return null|\Zend\Mvc\Router\Http\RouteMatch
     *
     * @todo move to external association matcher?
     */
    protected function matchMultiValueAssociation(Selectable $resource, ClassMetadata $metadata, $path)
    {
        $identifierValue = $this->firstPathChunk($path);

        if (empty($identifierValue)) {
            return null; // cannot filter if the identifier is empty
        }

        $identifierField = $metadata->getIdentifierFieldNames();

        if (count($metadata->getIdentifierFieldNames()) > 1) {
            return null; //composite identifiers not yet supported
        }

        $criteria = new Criteria();
        $criteria->andWhere(new Comparison(reset($identifierField), Comparison::EQ, $identifierValue));

        $newResource = $resource->matching($criteria);
        $subPath     = $this->trimFirstPathChunk($path);

        if ($this->isEmptyPath($subPath)) {
            // if this was the last segment, return a route match
            return new RouteMatch(array('resource' => $newResource), strlen($this->route));
        }

        if ($newResource->count() !== 1) {
            return null; // ambiguous or non existing result found
        }

        return $this->matchAssociation($newResource->first(), $metadata, $subPath);
    }

    /**
     * Matches a single valued association on a resource. If no value is available, returns null, otherwise
     * continues iterating over associations on the value until the path is completely crawled.
     *
     * @param \Doctrine\Common\Collections\Selectable            $resource
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $metadata
     * @param string                                             $path
     *
     * @return null|\Zend\Mvc\Router\Http\RouteMatch
     *
     * @todo move to external association matcher?
     */
    protected function matchSingleValueAssociation(Selectable $resource, ClassMetadata $metadata, $path)
    {
        $newResource = $resource->matching(new Criteria());

        if ($this->isEmptyPath($path)) {
            // if this was the last segment, return a route match
            // ignore cases where the fetched resource is not singular
            $newResource = $newResource->count() === 1 ? $newResource->first() : null;

            return new RouteMatch(array('resource' => $newResource), strlen($this->route));
        }

        if ($newResource->isEmpty()) {
            return null;
        }

        return $this->matchAssociation($newResource->first(), $metadata, $path);
    }

    /**
     * @param string $path
     *
     * @return bool
     */
    private function isEmptyPath($path)
    {
        return ('' === trim($path, '/'));
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function firstPathChunk($path)
    {
        list($chunk) = explode('/', ltrim($path, '/'));

        return $chunk;
    }

    /**
     * @param string $path
     *
     * @return string
     */
    private function trimFirstPathChunk($path)
    {
        $chunk   = $this->firstPathChunk($path);
        $trimmed = substr(ltrim($path, '/'), strlen($chunk));

        return $trimmed;
    }
}
