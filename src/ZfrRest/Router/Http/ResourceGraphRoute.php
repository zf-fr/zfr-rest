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

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use DoctrineModule\Paginator\Adapter\Collection as CollectionAdapter;
use DoctrineModule\Paginator\Adapter\Selectable as SelectableAdapter;
use Metadata\MetadataFactory;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Mvc\Router\RouteInterface;
use Zend\Paginator\Paginator;
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
class ResourceGraphRoute implements RouteInterface
{
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
        $this->route           = $route;
    }

    /**
     * {@inheritDoc}
     */
    public function assemble(array $params = array(), array $options = array())
    {
        // @TODO: not sure about how to do this correctly...

        throw new RuntimeException('ResourceGraphRoute does not support yet assembling route');
    }

    /**
     * {@inheritDoc}
     */
    public static function factory($options = array())
    {
        throw new RuntimeException('Not supported');
    }

    /**
     * {@inheritDoc}
     */
    public function match(RequestInterface $request)
    {
        if (!$request instanceof HttpRequest) {
            return null;
        }

        $uri  = $request->getUri();
        $path = trim($uri->getPath(), '/');

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

        if (!$match = $this->subPathMatcher->matchSubPath($this->getResource(), $subPath, $request)) {
            return null;
        }

        return $this->buildRouteMatch($match->getMatchedResource(), $this->route);
    }

    /**
     * Build a route match
     *
     * @param  ResourceInterface $resource
     * @param  string            $route
     * @throws RuntimeException
     * @return RouteMatch
     */
    protected function buildRouteMatch(ResourceInterface $resource, $route)
    {
        $metadata = $resource->getMetadata();

        // If returned $data is a collection, then we use the controller specified in Collection mapping
        if ($resource->isCollection()) {
            if (!$collectionMetadata = $metadata->getCollectionMetadata()) {
                throw new RuntimeException(
                    'No collection metadata could be found. Did you make sure you added the Collection annotation?'
                );
            }

            // We wrap the data around a paginator
            $paginator = $this->wrapDataInPaginator($resource);
            $resource  = new Resource($paginator, $metadata);

            $controllerName = $collectionMetadata->getControllerName();
        } else {
            $controllerName = $metadata->getControllerName();
        }

        return new RouteMatch(
            array(
                'resource'   => $resource,
                'controller' => $controllerName
            ),
            strlen($route)
        );
    }

    /**
     * Wrap a data around a paginator
     *
     * @param  ResourceInterface $resource
     * @return Paginator
     * @throws RuntimeException If no paginator adapter is found
     */
    protected function wrapDataInPaginator(ResourceInterface $resource)
    {
        $data             = $resource->getData();
        $paginatorAdapter = null;

        if ($data instanceof Selectable) {
            $paginatorAdapter = new SelectableAdapter($data);
        } elseif ($data instanceof Collection) {
            $paginatorAdapter = new CollectionAdapter($data);
        }

        if (null === $paginatorAdapter) {
            throw new RuntimeException(sprintf(
                'No paginator adapter could be found for resource of type "%s"',
                is_object($data) ? get_class($data) : gettype($data)
            ));
        }

        return new Paginator($paginatorAdapter);
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

        return $this->resource = new Resource($this->resource, $metadata);
    }
}
