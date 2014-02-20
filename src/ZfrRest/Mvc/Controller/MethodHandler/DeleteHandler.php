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

use ZfrRest\Http\Exception\Client\MethodNotAllowedException;
use ZfrRest\Mvc\Controller\AbstractRestfulController;
use ZfrRest\Resource\ResourceInterface;

/**
 * Handler for the DELETE method verb
 *
 * The DELETE request allow the client to delete a resource
 *
 * @link    http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.7
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class DeleteHandler implements MethodHandlerInterface
{
    /**
     * Handler for DELETE method
     *
     * DELETE method is used to delete a representation of a resource
     *
     * {@inheritDoc}
     */
    public function handleMethod(AbstractRestfulController $controller, ResourceInterface $resource)
    {
        // If no delete method is defined on the controller, then we cannot do anything
        if (!method_exists($controller, 'delete')) {
            throw new MethodNotAllowedException();
        }

        $result = $controller->delete($resource->getData());

        // According to http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.7, status code should
        // be 204 if nothing is returned
        if (empty($result)) {
            $controller->getResponse()->setStatusCode(204);
        }

        return $result;
    }
}
