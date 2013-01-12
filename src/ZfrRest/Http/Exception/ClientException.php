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

use InvalidArgumentException;

/**
 * ClientException
 *
 * @license MIT
 * @since   0.0.1
 */
class ClientException extends AbstractHttpException
{
    /**
     * @var string
     */
    protected $reasonPhrase = 'A client error occurred';


    /**
     * @param  null|int $statusCode
     * @param  string   $reasonPhrase
     * @throws InvalidArgumentException If status code is not 4xx
     */
    public function __construct($statusCode, $reasonPhrase = '')
    {
        // Client errors code are 4xx
        if ($statusCode < 400 || $statusCode > 499) {
            throw new InvalidArgumentException(sprintf(
                'Status code for client errors must be between 400 and 499, %s given',
                $statusCode
            ));
        }

        parent::__construct($statusCode, $reasonPhrase);
    }
}
