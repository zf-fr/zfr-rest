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
use Zend\Mvc\Router\Console\RouteInterface;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Stdlib\RequestInterface as Request;
use ZfrRest\Mvc\Exception;
use ZfrRest\Resource\Resource;
use ZfrRest\Resource\ResourceInterface;

class ResourceGraphRoute implements RouteInterface
{
    /**
     * @var \ZfrRest\Resource\ResourceInterface
     */
    protected $resource;

    /**
     * @var string
     */
    protected $path;


    /**
     * @todo consider passing in a resource name instead of the resource itself for performance
     *
     * @param \ZfrRest\Resource\ResourceInterface $resource
     * @param string                              $path
     */
    public function __construct(ResourceInterface $resource, $path)
    {
        $this->resource = $resource;
        $this->path     = trim($path, '/');
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
            return new RouteMatch(array('resource' => $this->resource), strlen($this->path));
        }

        if (0 !== strpos($path, $this->path) || !$this->resource->isCollection()) {
            return null;
        }

        return $this->matchIdentifier($this->resource, substr($path, strpos($path, '/')));
    }

    /**
     * @param  \ZfrRest\Resource\ResourceInterface $resource
     * @param  string                              $path
     * @return null|\Zend\Mvc\Router\Http\RouteMatch
     * @throws \RuntimeException
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
            $criteria = Criteria::expr()->eq(current($identifiers), array_shift($chunks));
            $resource = $resource->matching($criteria);
        }

        // We've processed the whole path
        if (empty($chunks)) {
            return new RouteMatch(array('resource' => $resource), strlen($path));
        }

        $resourceMetadata = ""; // TODO: inject the metadata factory
        $resource         = new Resource($resource, $resourceMetadata);

        return $this->matchAssociation($resource, substr($path, strpos($path, '/')));
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
        $associations     = $resourceMetadata->getAssociations();

        $chunks          = explode('/', $path);
        $associationName = array_shift($chunks);

        if (!isset($associations[$associationName])) {
            return null;
        }

        $refl         = $classMetadata->getReflectionClass();
        $reflProperty = $refl->getProperty($associationName);
        $reflProperty->setAccessible(true);

        $resource = $reflProperty->getValue($resource);

        $resourceMetadata = ""; // TODO: inject the metadata factory
        $resource         = new Resource($resource, $resourceMetadata);

        // We've processed the whole path
        if (empty($chunks)) {
            return new RouteMatch(array('resource' => $resource), strlen($path));
        }

        return $this->matchIdentifier($resource, substr($path, strpos($path, '/')));
    }
}
