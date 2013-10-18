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

namespace ZfrRest\Mvc\Controller\MethodHandler;

use Zend\Mvc\Controller\AbstractController;
use Zend\Stdlib\ResponseInterface;
use ZfrRest\Mvc\Controller\MethodHandler\MethodHandlerInterface;
use ZfrRest\Resource\ResourceInterface;

/**
 * Handler for the OPTIONS method verb
 *
 * The OPTIONS request allow the client to determine the options and requirements associated with
 * a resource.
 *
 * @link    http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.2
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class OptionsHandler implements MethodHandlerInterface
{
    /**
     * Handler for OPTIONS method
     *
     * OPTIONS handler returns all the available HTTP methods for a given resource, and return
     * a 200 OK status code
     *
     * @param  AbstractController $controller
     * @param  ResourceInterface $resource
     * @return ResponseInterface
     */
    public function handleMethod(AbstractController $controller, ResourceInterface $resource)
    {
        // For the OPTIONS verb, we have an out-of-the box implementation, but if it is
        // defined in the controller we use the user-land method instead
        if (method_exists($controller, 'options')) {
            $allowedMethods = $controller->options();
        } else {
            $allowedMethods = $this->getAllowedMethods($controller);
        }

        $response = $controller->getResponse();

        $response->getHeaders()->addHeaderLine('Allow', implode(', ', $allowedMethods));
        $response->setContent('');
        $response->setStatusCode(200);

        return $response;
    }

    /**
     * Get all the available methods
     *
     * By default, it will automatically returns the HTTP methods that are implemented. If you
     * are using custom HTTP verbs, you can override this method and return your own verbs
     *
     * @param  AbstractController $controller
     * @return array
     */
    protected function getAllowedMethods(AbstractController $controller)
    {
        $genericMethods = array('get', 'head', 'put', 'post', 'patch', 'delete', 'options');
        $methods        = array_intersect(get_class_methods($controller), $genericMethods);

        return $methods;
    }
}
