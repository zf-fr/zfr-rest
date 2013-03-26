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

use Zend\Http\Request as HttpRequest;
use Zend\Mvc\Controller\AbstractController;
use Zend\Mvc\Exception;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\RequestInterface;
use Zend\Stdlib\ResponseInterface;
use ZfrRest\Http\Exception\Client;
use ZfrRest\Resource\Metadata\ResourceMetadataInterface;

/**
 * Abstract RESTful controller. It is responsible for dispatching a HTTP request to a function, or throwing an
 * exception if the method is not implemented.
 *
 * By default, AbstractRestfulController handles the "big four" methods (GET, DELETE, PUT and POST). You can add
 * your own verbs or changing existing ones by overriding methods like handle*Method (eg.: handleGetMethod,
 * handlePostMethod, handlePatchMethod...). Please note that your handlers must always return the result of
 * the action!
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
        $method  = strtolower($this->getRequest()->getMethod());
        $handler = 'handle' . ucfirst($method) . 'Method';

        if (!method_exists($this, $method) || !method_exists($this, $handler)) {
            throw new Client\MethodNotAllowedException();
        }

        $resource = $e->getRouteMatch()->getParam('resource', null);
        $metadata = $e->getRouteMatch()->getParam('metadata', null);

        // We should always have a resource and metadata, otherwise throw an 404 exception
        if (null === $resource || null === $metadata) {
            throw new Client\NotFoundException();
        }

        $return = $this->$handler($resource, $metadata);

        $e->setResult($return);

        return $return;
    }

    /**
     * GET method is used to retrieve (or read) a representation of a resource. Get method is idempotant, which means
     * that making multiple identical requests ends up having the same result as a single request. Get requests should
     * not modify any resources
     *
     * @param  mixed                     $resource
     * @param  ResourceMetadataInterface $metadata
     * @return mixed
     */
    protected function handleGetMethod($resource, ResourceMetadataInterface $metadata)
    {
        return $this->get($resource, $metadata);
    }

    /**
     * DELETE method is used to delete a representation of a resource
     *
     * @param  mixed                     $resource
     * @param  ResourceMetadataInterface $metadata
     * @return mixed
     */
    protected function handleDeleteMethod($resource, ResourceMetadataInterface $metadata)
    {
        return $this->delete($resource, $metadata);
    }

    /**
     * POST method is used to create a new resource. On successful creation, POST method returns a HTTP status 201,
     * with a Location header containing the URL of the newly created resource
     *
     * @param  mixed                     $resource
     * @param  ResourceMetadataInterface $metadata
     * @return mixed
     */
    protected function handlePostMethod($resource, ResourceMetadataInterface $metadata)
    {
      //  $
    }

    /**
     * Parse the body according to the Content-Type value
     *
     * @return array|null
     */
    protected function parseBody()
    {
        return $this->decode($this->request->getContent());
    }

    /**
     * Parse the post array according to the Content-Type value
     *
     * @return array|null
     */
    protected function parsePost()
    {
        return $this->decode($this->request->getPost());
    }

    /**
     * Decode a content according to the Content-Type value
     *
     * @param  mixed $content
     * @return array
     */
    protected function decode($content)
    {
        /** @var $decoderPluginManager \ZfrRest\Serializer\DecoderPluginManager */
        $decoderPluginManager = $this->serviceLocator('ZfrRest\Serializer\DecoderPluginManager');

        $header = $this->request->getHeader('Content-Type', null);
        if ($header === null) {
            return null;
        }

        $mimeType = $header->getFieldValue();

        return $decoderPluginManager->get($mimeType)->decode($content);
    }
}
