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

namespace ZfrRestTest\Http\Exception\Client;

use PHPUnit_Framework_TestCase;
use ZfrRest\Http\Exception;

/**
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group Coverage
 * @covers \ZfrRest\Http\Exception\Client\BadRequestException
 * @covers \ZfrRest\Http\Exception\Client\ConflictException
 * @covers \ZfrRest\Http\Exception\Client\ForbiddenException
 * @covers \ZfrRest\Http\Exception\Client\GoneException
 * @covers \ZfrRest\Http\Exception\Client\MethodNotAllowedException
 * @covers \ZfrRest\Http\Exception\Client\NotFoundException
 * @covers \ZfrRest\Http\Exception\Client\UnauthorizedException
 */
class ClientExceptionsTest extends PHPUnit_Framework_TestCase
{
    public function exceptionProvider()
    {
        return [
            [
                'exception'  => Exception\Client\BadRequestException::class,
                'statusCode' => 400
            ],
            [
                'exception'  => Exception\Client\ConflictException::class,
                'statusCode' => 409
            ],
            [
                'exception'  => Exception\Client\ForbiddenException::class,
                'statusCode' => 403
            ],
            [
                'exception'  => Exception\Client\GoneException::class,
                'statusCode' => 410
            ],
            [
                'exception'  => Exception\Client\MethodNotAllowedException::class,
                'statusCode' => 405
            ],
            [
                'exception'  => Exception\Client\NotFoundException::class,
                'statusCode' => 404
            ],
            [
                'exception'  => Exception\Client\UnauthorizedException::class,
                'statusCode' => 401
            ]
        ];
    }

    /**
     * @dataProvider exceptionProvider
     */
    public function testClientException($exception, $statusCode)
    {
        /* @var \ZfrRest\Http\Exception\ClientErrorException $exception */
        $exception = new $exception();

        $this->assertEquals($statusCode, $exception->getCode());
    }
}
