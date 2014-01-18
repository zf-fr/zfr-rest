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

namespace ZfrRest\Router\Http;

use Doctrine\Common\Persistence\ObjectRepository;
use Metadata\MetadataFactory;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerAwareTrait;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Mvc\Router\Http\RouteInterface;
use Zend\Stdlib\RequestInterface;
use ZfrRest\Resource\Resource;
use ZfrRest\Resource\ResourceInterface;
use ZfrRest\Router\Exception\RuntimeException;
use ZfrRest\Router\Http\Matcher\BaseSubPathMatcher;

/**
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class ResourceGraphRoute implements RouteInterface, EventManagerAwareInterface
{
    use EventManagerAwareTrait;

    /**
     * @var MetadataFactory
     */
    protected $metadataFactory;

    /**
     * @var mixed
     */
    protected $resource;

    /**
     * @var BaseSubPathMatcher
     */
    protected $subPathMatcher;

    /**
     * @var string
     */
    protected $route;

    /**
     * Constructor
     *
     * @param MetadataFactory    $metadataFactory
     * @param BaseSubPathMatcher $matcher
     * @param mixed              $resource
     * @param string             $route
     */
    public function __construct(MetadataFactory $metadataFactory, BaseSubPathMatcher $matcher, $resource, $route)
    {
        $this->metadataFactory = $metadataFactory;
        $this->subPathMatcher  = $matcher;
        $this->resource        = $resource;
        $this->route           = ltrim($route, '/');
    }

    /**
     * {@inheritDoc}
     */
    public function assemble(array $params = [], array $options = [])
    {
        // We cannot do anything if no resource is given...
        if (!isset($params['resource'])) {
            throw new RuntimeException('ResourceGraphRoute cannot assemble without resource');
        }

        /* @var \ZfrRest\Resource\ResourceInterface $resource */
        $resource = $params['resource'];

        $classMetadata = $resource->getMetadata()->getClassMetadata();
        $identifiers   = $classMetadata->getIdentifierValues($resource->getData());

        return trim($this->route, '/') . '/' . current($identifiers);
    }

    /**
     * {@inheritDoc}
     */
    public function getAssembledParams()
    {
        throw new RuntimeException('ResourceGraphRoute does not support yet assembling route');
    }

    /**
     * {@inheritDoc}
     */
    public static function factory($options = [])
    {
        throw new RuntimeException('Not supported');
    }

    /**
     * {@inheritDoc}
     */
    public function match(RequestInterface $request, $pathOffset = 0)
    {
        if (!$request instanceof HttpRequest) {
            return null;
        }

        $uri  = $request->getUri();
        $path = trim(substr($uri->getPath(), $pathOffset), '/');

        // We must omit the basePath
        if (method_exists($request, 'getBaseUrl') && $baseUrl = $request->getBaseUrl()) {
            $path = substr($path, strlen(trim($baseUrl, '/')));
        }

        // If the URI does not begin by the route, we can stop immediately
        if (substr($path, 0, strlen($this->route)) !== $this->route) {
            return null;
        }

        // If we have only one segment (for instance "users"), then the next path to analyze is in fact
        // an empty string, hence the ternary condition
        $pathParts = explode('/', $path, 2);
        $subPath   = count($pathParts) === 1 ? '' : end($pathParts);

        if (!$match = $this->subPathMatcher->matchSubPath($this->getResource(), $subPath)) {
            // Although this is an error, we still want to create a route match, so that the request
            // can continue, and that we can do more error handling
            return new RouteMatch([
                'controller' => $this->resource->getMetadata()->getControllerName()
            ]);
        }

        return $this->buildRouteMatch($match->getMatchedResource());
    }

    /**
     * Build a route match
     *
     * @param  ResourceInterface $resource
     * @throws RuntimeException
     * @return RouteMatch
     */
    protected function buildRouteMatch(ResourceInterface $resource)
    {
        $metadata = $resource->getMetadata();

        // If returned $data is a collection, then we use the controller specified in Collection mapping
        if ($resource->isCollection()) {
            if (!$collectionMetadata = $metadata->getCollectionMetadata()) {
                throw new RuntimeException(
                    'No collection metadata could be found. Did you make sure you added the Collection annotation?'
                );
            }
            $controllerName = $collectionMetadata->getControllerName();
        } else {
            $controllerName = $metadata->getControllerName();
        }

        return new RouteMatch(
            [
                'resource'   => $resource,
                'controller' => $controllerName
            ],
            strlen($this->route)
        );
    }

    /**
     * Initialize the resource to create an object implementing the ResourceInterface interface. A resource can
     * be anything: an entity, a collection, a Selectable... However, any ResourceInterface object contains both
     * the resource AND metadata associated to it. This metadata is usually extracted from the entity name
     *
     * @throws RuntimeException
     * @return ResourceInterface
     */
    private function getResource()
    {
        // Don't initialize twice
        if ($this->resource instanceof ResourceInterface) {
            return $this->resource;
        }

        if ($this->resource instanceof ObjectRepository) {
            $metadata = $this->metadataFactory->getMetadataForClass($this->resource->getClassName());
        } elseif (is_string($this->resource)) {
            $metadata = $this->metadataFactory->getMetadataForClass($this->resource);
        } else {
            throw new RuntimeException(sprintf(
                'Resource "%s" is not supported: either specify an ObjectRepository instance, or an entity class name',
                is_object($this->resource) ? get_class($this->resource) : gettype($this->resource)
            ));
        }

        return $this->resource = new Resource($this->resource, $metadata->getOutsideClassMetadata());
    }
}
