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
use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectManager;
use Metadata\MetadataFactory;
use Zend\Mvc\Router\Http\RouteInterface;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Stdlib\RequestInterface as Request;
use ZfrRest\Mvc\Exception;
use ZfrRest\Resource\Resource;
use ZfrRest\Resource\ResourceInterface;

class ResourceGraphRoute implements RouteInterface
{
    /**
     * @var \Metadata\MetadataFactory
     */
    protected $metadataFactory;

    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;

    /**
     * @var \ZfrRest\Resource\ResourceInterface
     */
    protected $resource;

    /**
     * @var string
     */
    protected $path;

    /**
     * @var array
     */
    protected $query;


    /**
     * @param \Metadata\MetadataFactory                  $metadataFactory
     * @param \Doctrine\Common\Persistence\ObjectManager $objectManager
     * @param string                                     $resource
     * @param string                                     $path
     */
    public function __construct(MetadataFactory $metadataFactory, ObjectManager $objectManager, $resource, $path)
    {
        $this->metadataFactory = $metadataFactory;
        $this->objectManager   = $objectManager;
        $this->path            = trim($path, '/');

        $resourceMetadata = $this->metadataFactory->getMetadataForClass($resource)->getRootClassMetadata();
        $resource         = $this->objectManager->getRepository($resource);

        $this->resource   = new Resource($resource, $resourceMetadata);
    }

    /**
     * {@inheritDoc}
     */
    public function assemble(array $params = array(), array $options = array())
    {
        // TODO: Implement assemble() method.
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
        throw new \BadMethodCallException('Not supported');
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

        if ($path === $this->path) {
            return $this->createRouteMatch($this->resource, $this->path);
        }

        if (0 !== strpos($path, $this->path) || !$this->resource->isCollection()) {
            return null;
        }

        $this->query = $uri->getQueryAsArray();

        return $this->matchIdentifier($this->resource, substr($path, strpos($path, '/')));
    }

    /**
     * @param  \ZfrRest\Resource\ResourceInterface $resource
     * @param  string                              $path
     * @throws \ZfrRest\Mvc\Exception\RuntimeException
     * @return null|\Zend\Mvc\Router\Http\RouteMatch
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

        if ($resource instanceof Selectable) {
            $expression = Criteria::expr()->eq(current($identifiers), array_shift($chunks));
            $resource   = $resource->matching(new Criteria($expression))->first();
        }

        $this->resource = new Resource($resource, $this->resource->getMetadata());

        // We've processed the whole path
        if (empty($chunks)) {
            return $this->createRouteMatch($this->resource, $path);
        }

        return $this->matchAssociation($this->resource, substr($path, strpos($path, '/')));
    }

    /**
     * @param  \ZfrRest\Resource\ResourceInterface $resource
     * @param  string                              $path
     * @return null|\Zend\Mvc\Router\Http\RouteMatch
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

        // We've processed the whole path
        if (empty($chunks)) {
            // Filter by query
            if (!empty($this->query) && $resource instanceof Selectable) {
                $criteria = Criteria::create();

                foreach ($this->query as $key => $value) {
                    $criteria->andWhere(Criteria::expr()->eq($key, $value));
                }

                $resource = $resource->matching($criteria);

                $resourceMetadata = $resourceMetadata->getAssociationMetadata($associationName);
                $this->resource   = new Resource($resource, $resourceMetadata);
            }

            return $this->createRouteMatch($this->resource, $path);
        }

        $resourceMetadata = $resourceMetadata->getAssociationMetadata($associationName);
        $this->resource   = new Resource($resource, $resourceMetadata);


        return $this->matchIdentifier($this->resource, substr($path, strpos($path, '/')));
    }

    /**
     * @param  ResourceInterface $resource
     * @param  $path
     * @return RouteMatch
     */
    protected function createRouteMatch(ResourceInterface $resource, $path)
    {
        $controller = $resource->getMetadata()->getControllerName();
        return new RouteMatch(array('resource' => $resource, 'controller' => $controller), strlen($path));
    }
}
