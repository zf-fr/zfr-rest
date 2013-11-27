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

namespace ZfrRestTest\Http\Exception;

use PHPUnit_Framework_TestCase;
use Zend\Http\Response as HttpResponse;
use ZfrRestTest\Asset\HttpException\SimpleException;

/**
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group Coverage
 * @covers \ZfrRest\Http\Exception\AbstractHttpException
 */
class ExceptionTest extends PHPUnit_Framework_TestCase
{
    public function testFillResponse()
    {
        $exception = new SimpleException(400, 'Validation errors');
        $response  = new HttpResponse();

        $exception->prepareResponse($response);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Validation errors', $response->getReasonPhrase());
    }

    public function testCanSetAndGetErrors()
    {
        $exception = new SimpleException();
        $exception->setErrors(['foo' => 'bar']);

        $this->assertEquals(['foo' => 'bar'], $exception->getErrors());
    }
}
