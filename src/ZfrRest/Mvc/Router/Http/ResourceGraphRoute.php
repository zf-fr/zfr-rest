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

use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use DoctrineModule\Paginator\Adapter\Selectable as SelectableAdapter;
use Metadata\MetadataFactory;
use Zend\Mvc\Router\Http\RouteInterface;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Paginator\Paginator;
use Zend\Stdlib\RequestInterface as Request;
use ZfrRest\Mvc\Exception;
use ZfrRest\Resource\Resource;
use ZfrRest\Resource\ResourceInterface;

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
     * @var ResourceInterface|mixed
     */
    protected $resource;

    /**
     * Entry point route
     *
     * @var string
     */
    protected $route;

    /**
     * Optional GET parameters that are extracted from the request
     *
     * @var array
     */
    protected $query;


    /**
     * @param MetadataFactory $metadataFactory
     * @param mixed           $resource
     * @param string          $route
     */
    public function __construct(MetadataFactory $metadataFactory, $resource, $route)
    {
        $this->metadataFactory = $metadataFactory;
        $this->route           = trim($route, '/');
        $this->resource        = $resource;
    }

    /**
     * {@inheritDoc}
     */
    public function assemble(array $params = array(), array $options = array())
    {
        var_dump($this->resource);
    }

    /**
     * {@inheritDoc}
     */
    public function getAssembledParams()
    {
        // TODO: Implement getAssembledParams() method.
    }

    /**
     * {@inheritDoc}
     */
    public static function factory($options = array())
    {
        throw new Exception\BadMethodCallException('Not supported');
    }

    /**
     * {@inheritDoc}
     */
    public function match(Request $request)
    {
        if (!method_exists($request, 'getUri')) {
            return null;
        }

        /* @var $request \Zend\Http\Request */
        $uri  = $request->getUri();
        $path = trim($uri->getPath(), '/');

        // Save the query part (GET parameters) to optionally filter the result at the end
        $this->query = $uri->getQueryAsArray();

        // If the route is not even contained within the URI, this means we can return early...
        if (strpos($path, $this->route) === false) {
            return null;
        }

        // ...and we can now initialize the resource
        $this->initializeResource();

        if ($path === $this->route) {
            return $this->buildRouteMatch($this->resource, $path);
        }

        if (!$this->resource->isCollection()) {
            return null;
        }

        return $this->matchIdentifier($this->resource, substr($path, strpos($path, '/')));
    }

    /**
     * @param  ResourceInterface $resource
     * @param  string            $path
     * @throws Exception\RuntimeException
     * @return RouteMatch|null
     */
    protected function matchIdentifier(ResourceInterface $resource, $path)
    {
        $path          = trim($path, '/');
        $classMetadata = $resource->getMetadata()->getClassMetadata();
        $identifiers   = $classMetadata->getIdentifierFieldNames();

        if (count($identifiers) > 1) {
            throw new Exception\RuntimeException('Composite identifiers are not currently supported by ZfrRest');
        }

        $resource = $resource->getResource();
        $chunks   = explode('/', $path);

        // Favor Repository over Selectable as it allows to call custom repository methods
        if ($resource instanceof ObjectRepository) {
            $resource = $resource->find(array_shift($chunks));
        } elseif ($resource instanceof Selectable) {
            $expression = Criteria::expr()->eq(current($identifiers), array_shift($chunks));
            $resource   = $resource->matching(new Criteria($expression))->first();
        }

        if (null === $resource) {
            return $this->buildErrorRouteMatch($this->resource, $path);
        }

        // We matched an identifier, so the metadata stay the same (but we moved from a Collection to
        // a single item)
        $this->resource = new Resource($resource, $this->resource->getMetadata());

        // If empty, then we have processed the whole path
        if (empty($chunks)) {
            return $this->buildRouteMatch($this->resource, $path);
        }

        return $this->matchAssociation($this->resource, substr($path, strpos($path, '/')));
    }

    /**
     * @param  ResourceInterface $resource
     * @param  string            $path
     * @return RouteMatch|null
     */
    protected function matchAssociation(ResourceInterface $resource, $path)
    {
        $path             = trim($path, '/');
        $resourceMetadata = $resource->getMetadata();
        $classMetadata    = $resourceMetadata->getClassMetadata();

        $chunks          = explode('/', $path);
        $associationName = array_shift($chunks);

        if (!$resourceMetadata->hasAssociation($associationName)) {
            return null;
        }

        $refl         = $classMetadata->getReflectionClass();
        $reflProperty = $refl->getProperty($associationName);
        $reflProperty->setAccessible(true);

        $resource = $reflProperty->getValue($resource->getResource());

        $resourceMetadata = $resourceMetadata->getAssociationMetadata($associationName);
        $this->resource   = new Resource($resource, $resourceMetadata);

        // If empty, we have processed the whole path
        if (empty($chunks)) {
            return $this->buildRouteMatch($this->resource, $path);
        }

        return $this->matchIdentifier($this->resource, substr($path, strpos($path, '/')));
    }

    /**
     * Build a route match. This function extract the controller from the resource metadata, and does
     * optional filtering by query
     *
     * @param  ResourceInterface $resource
     * @param  string            $path
     * @return RouteMatch
     */
    protected function buildRouteMatch(ResourceInterface $resource, $path)
    {
        $classMetadata    = $resource->getMetadata()->getClassMetadata();
        $resourceMetadata = $resource->getMetadata();
        $resource         = $resource->getResource();

        if ($resource instanceof Selectable) {
            $criteria = Criteria::create();

            foreach ($this->query as $key => $value) {
                if ($classMetadata->hasField($key)) {
                    $criteria->andWhere(Criteria::expr()->eq($key, $value));
                }
            }

            if ($resourceMetadata->shouldPaginateCollection()) {
                $resource = new Paginator(new SelectableAdapter($resource, $criteria));
            } else {
                $resource = $resource->matching($criteria);
            }
        }

        // If returned resource is a Collection, then we use the controller specified in Collection mapping
        if ($resource instanceof Paginator || $resource instanceof Collection) {
            $controllerName = $resourceMetadata->getCollectionControllerName();
        } else {
            $controllerName = $resourceMetadata->getControllerName();
        }

        return new RouteMatch(array(
            'resource'   => $resource,
            'metadata'   => $resourceMetadata,
            'controller' => $controllerName
        ), strlen($path));
    }

    /**
     * Build an error route match. This can happen if, for instance, no object was found after matching an
     * identifier. However, we still want to dispatch to the controller so that we can do further error handling
     *
     * @param  ResourceInterface $resource
     * @param  string            $path
     * @return RouteMatch
     */
    protected function buildErrorRouteMatch(ResourceInterface $resource, $path)
    {
        return new RouteMatch(array(
            'controller' => $resource->getMetadata()->getControllerName()
        ), strlen($path));
    }

    /**
     * Initialize the resource to create an object implementing the ResourceInterface interface. A resource can
     * be anything: an entity, a collection, a Selectable... However, any ResourceInterface object contains both
     * the resource AND metadata associated to it. This metadata is usually extracted from the entity name
     *
     * @return void
     */
    private function initializeResource()
    {
        // Don't initialize twice
        if ($this->resource instanceof ResourceInterface) {
            return;
        }

        /** @var $metadata \Metadata\ClassHierarchyMetadata */
        $metadata = null;
        $resource = $this->resource;

        if ($resource instanceof ObjectRepository) {
            $metadata = $this->metadataFactory->getMetadataForClass($resource->getClassName());
        } else {
            $metadata = $this->metadataFactory->getMetadataForClass($resource);
        }

        $this->resource = new Resource($resource, $metadata->getRootClassMetadata());
    }
}
