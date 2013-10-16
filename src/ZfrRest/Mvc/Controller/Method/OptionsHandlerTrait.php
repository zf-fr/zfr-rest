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

namespace ZfrRest\Mvc\Controller\Method;

use Zend\Stdlib\ResponseInterface;
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
trait OptionsHandlerTrait
{
    /**
     * Handler for OPTIONS method
     *
     * OPTIONS handler returns all the available HTTP methods for a given resource, and return
     * a 200 OK status code
     *
     * @param  ResourceInterface $resource
     * @return ResponseInterface
     */
    public function handleOptionsMethod(ResourceInterface $resource)
    {
        $allowedMethods = $this->options();

        foreach ($allowedMethods as &$allowedMethod) {
            $allowedMethod = strtoupper($allowedMethod);
        }

        $this->response->getHeaders()->addHeaderLine('Allow', implode(', ', $allowedMethods));
        $this->response->setStatusCode(200);

        return $this->response;
    }

    /**
     * Get all the available methods
     *
     * By default, it will automatically returns the HTTP methods that are implemented. If you
     * are using custom HTTP verbs, you can override this method and return your own verbs
     *
     * @return array
     */
    public function options()
    {
        $genericMethods = array('get', 'head', 'put', 'post', 'patch', 'delete', 'options');
        $methods        = array_intersect(get_class_methods($this), $genericMethods);

        return $methods;
    }
}
