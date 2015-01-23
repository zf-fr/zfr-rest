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

namespace ZfrRest\Http\Exception\Client;

use Zend\Http\Response as HttpResponse;
use ZfrRest\Http\Exception\ClientErrorException;

/**
 * MethodNotAllowedException
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class MethodNotAllowedException extends ClientErrorException
{
    /**
     * @var string
     */
    const DEFAULT_MESSAGE = 'A request was made using a HTTP method not supported by that resource';

    /**
     * @var array
     */
    private $allowedMethods = [];

    /**
     * @param string $message
     * @param mixed  $errors
     * @param array  $allowedMethods
     */
    public function __construct($message = '', $errors = null, $allowedMethods = [])
    {
        parent::__construct(405, $message, $errors);
        $this->allowedMethods = $allowedMethods;
    }

    /**
     * Add the available methods (if any) to the Allow header
     *
     * {@inheritDoc}
     */
    public function prepareResponse(HttpResponse $response)
    {
        parent::prepareResponse($response);

        if (empty($this->allowedMethods)) {
            return;
        }

        $headers = $response->getHeaders();
        $headers->addHeaderLine('Allow', implode(', ', $this->allowedMethods));
    }
}
