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

namespace ZfrRest\Http\Exception\Server;

use Exception;
use ZfrRest\Http\Exception\ServerException;

/**
 * InternalServerErrorException
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class InternalServerErrorException extends ServerException
{
    /**
     * @var string
     */
    protected $message = 'An internal server error occurred';


    /**
     * @param string $message
     * @param mixed  $errors
     */
    public function __construct($message = '', $errors = '')
    {
        parent::__construct(500, $message, $errors);
    }

    /**
     * @return self
     */
    public static function missingInputFilter()
    {
        return new self('No input filter class was given, although controller is configured to auto validate');
    }

    /**
     * @param string    $inputFilterName
     * @param Exception|null $previous
     *
     * @return self
     *
     * @todo should handle also $previous exception
     */
    public static function invalidInputFilter($inputFilterName, Exception $previous = null)
    {
        return new self(
            sprintf('An invalid input filter class name was given when validating data ("%s" given)', $inputFilterName)
        );
    }

    /**
     * @return self
     */
    public static function missingHydrator()
    {
        return new self('No hydrator was given, although controller is configured to auto hydrate');
    }

    /**
     * @param string         $hydratorName
     * @param Exception|null $previous
     *
     * @return self
     *
     * @todo should handle also $previous exception
     */
    public static function invalidHydrator($hydratorName, Exception $previous = null)
    {
        return new self(
            sprintf('An invalid hydrator class name was given when hydrating data ("%s" given)', $hydratorName)
        );
    }
}
