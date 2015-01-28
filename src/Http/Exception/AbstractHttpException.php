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
use Zend\Http\Response as HttpResponse;

/**
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
abstract class AbstractHttpException extends Exception implements HttpExceptionInterface
{
    /**
     * Message used for the exception if none is specified
     *
     * @var string
     */
    const DEFAULT_MESSAGE = 'An error occurred';

    /**
     * Additional errors that should be sent (for instance, input filter errors)
     *
     * @var mixed
     */
    protected $errors;

    /**
     * Constructor
     *
     * @param null|int   $statusCode
     * @param string     $message
     * @param mixed|null $errors
     */
    public function __construct($statusCode = null, $message = '', $errors = null)
    {
        if (empty($message)) {
            $message = static::DEFAULT_MESSAGE;
        }

        parent::__construct($message, $statusCode);
        $this->errors = $errors;
    }

    /**
     * {@inheritDoc}
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * {@inheritDoc}
     */
    public function setErrors($errors)
    {
        $this->errors = $errors;
    }

    /**
     * {@inheritDoc}
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * {@inheritDoc}
     */
    public function prepareResponse(HttpResponse $response)
    {
        $response->setStatusCode($this->getCode());
        $response->setReasonPhrase($this->getMessage());
    }
}
