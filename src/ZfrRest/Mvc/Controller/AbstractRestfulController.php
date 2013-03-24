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

namespace ZfrRest\Mvc\Controller;

use Traversable;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\Controller\AbstractController;
use Zend\Mvc\Exception;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;
use ZfrRest\Http\Exception\Client;

/**
 * Abstract RESTful controller. It is responsible for dispatching a HTTP request to a function, or throwing an
 * exception if the method is not implemented.
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
abstract class AbstractRestfulController extends AbstractController
{
    /**
     * {@inheritDoc}
     */
    public function dispatch(RequestInterface $request, ResponseInterface $response = null)
    {
        if (!$request instanceof HttpRequest) {
            throw new Exception\InvalidArgumentException('Expected an HTTP request');
        }

        return parent::dispatch($request, $response);
    }

    /**
     * Execute the request. Try to match the HTTP verb to an action
     *
     * @param  MvcEvent $e
     * @return mixed
     * @throws Client\NotFoundException If the resource cannot be found
     * @throws Client\MethodNotAllowedException If the method to handle the request is not implemented
     */
    public function onDispatch(MvcEvent $e)
    {
        $method = strtolower($this->getRequest()->getMethod());
        if (!method_exists($this, $method)) {
            throw new Client\MethodNotAllowedException();
        }

        /** @var $resource \ZfrRest\Resource\ResourceInterface|null */
        $resource = $e->getRouteMatch()->getParam('resource', null);

        // We should always have a resource, otherwise throw an 404 exception
        if (null === $resource) {
            throw new Client\NotFoundException();
        }

        if($resource instanceof Traversable || is_array($resource)) {
            $method .= 'List';
        }

        $return = $this->$method($resource, $e->getRouteMatch()->getParam('metadata'));

        $e->setResult($return);

        return $return;
    }
}
