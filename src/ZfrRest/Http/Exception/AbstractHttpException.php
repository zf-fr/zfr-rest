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

namespace ZfrRest\Http\Exception;

use Exception;
use Zend\Http\Exception\ExceptionInterface as HttpExceptionInterface;
use Zend\Http\Response as HttpResponse;
use ZfrRest\Exception\ExceptionInterface;

/**
 * ExceptionInterface
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
abstract class AbstractHttpException extends Exception implements ExceptionInterface, HttpExceptionInterface
{
    /**
     * @var int
     */
    protected $statusCode;

    /**
     * @var string
     */
    protected $message = 'An error occurred';


    /**
     * @param null|int $statusCode
     * @param string   $message
     */
    public function __construct($statusCode = null, $message = '')
    {
        if ($statusCode !== null) {
            $this->setStatusCode($statusCode);
        }

        if ($message !== '') {
            $this->message = $message;
        }
    }

    /**
     * Set the status code of the HTTP error
     *
     * @param  int $statusCode
     * @return AbstractHttpException
     */
    public function setStatusCode($statusCode)
    {
        $this->statusCode = (int) $statusCode;
        return $this;
    }

    /**
     * Get the status code of the HTTP error
     *
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * Prepare the response for the exception
     *
     * @param  HttpResponse $response
     * @return void
     */
    public function prepareResponse(HttpResponse $response)
    {
        $response->setStatusCode($this->getStatusCode());
        $response->setReasonPhrase($this->getMessage());
    }
}
