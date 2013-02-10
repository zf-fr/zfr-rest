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

use Doctrine\Common\Collections\Selectable;
use Zend\Mvc\Router\Console\RouteInterface;
use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Stdlib\RequestInterface as Request;
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

        /*if ($path === $this->path) {
            return new RouteMatch(array('resource' => $this->resource), strlen($this->path));
        }*/

        $chunks = explode('/', $path);

        if (0 !== strpos($path, $this->path)) {
            return null;
        }

        if (!$this->resource->isCollection()) {
            return null;
        }

        return $this->matchMultiValueAssociation(
            $this->resource,
            $this->resourceManager->getResourceClassMetadata($this->resourceName),
            substr($path, $offset),
            $offset
        );
    }
}
