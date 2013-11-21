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

namespace ZfrRestTest\Mvc;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\MvcEvent;
use ZfrRest\Mvc\HttpExceptionListener;
use ZfrRest\Http\Exception;

/**
 * @author Michaël Gallego <mic.gallego@gmail.com>
 * @covers \ZfrRest\Mvc\HttpExceptionListener
 */
class HttpExceptionListenerTest extends TestCase
{
    /**
     * @var HttpExceptionListener
     */
    protected $httpExceptionListener;

    /**
     * @var HttpResponse
     */
    protected $response;

    /**
     * @var MvcEvent
     */
    protected $event;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->httpExceptionListener = new HttpExceptionListener();

        // Init the MvcEvent object
        $this->response = new HttpResponse();

        $this->event = new MvcEvent();
        $this->event->setResponse($this->response);
    }

    public function testCorrectlySetStatusCodeIfHttpExceptionIsRaised()
    {
        $error = new Exception\ClientException(404);
        $this->event->setParam('exception', $error);

        $this->httpExceptionListener->onDispatchError($this->event);

        $this->assertEquals(404, $this->response->getStatusCode());
        $this->assertEquals('A client error occurred', $this->response->getReasonPhrase());
    }

    public function testCorrectlySetReasonPhraseIfHttpExceptionIsRaised()
    {
        $error = new Exception\ServerException(500);
        $this->event->setParam('exception', $error);

        $this->httpExceptionListener->onDispatchError($this->event);

        $this->assertEquals(500, $this->response->getStatusCode());
        $this->assertEquals('A server error occurred', $this->response->getReasonPhrase());
    }

    public function testAssertWWWAuthenticateHeaderIsAutomaticallyAddedWhenUnauthorizedExceptionIsRaised()
    {
        $error = new Exception\Client\UnauthorizedException();
        $this->event->setParam('exception', $error);

        $this->httpExceptionListener->onDispatchError($this->event);

        $this->assertEquals(401, $this->response->getStatusCode());
        $this->assertEquals(
            'You are not authorized to access to the requested resource',
            $this->response->getReasonPhrase()
        );
        $this->assertTrue($this->response->getHeaders()->has('WWWAuthenticate'));
    }
}
