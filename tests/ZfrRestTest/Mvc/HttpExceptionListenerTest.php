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

use PHPUnit_Framework_TestCase;
use Zend\Http\Response as HttpResponse;
use Zend\Mvc\MvcEvent;
use ZfrRest\Http\Exception;
use ZfrRest\Mvc\HttpExceptionListener;

/**
 * @licence MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group Coverage
 * @covers \ZfrRest\Mvc\HttpExceptionListener
 */
class HttpExceptionListenerTest extends PHPUnit_Framework_TestCase
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

    public function testAttachToCorrectEvent()
    {
        $eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');
        $eventManager->expects($this->once())->method('attach')->with(MvcEvent::EVENT_DISPATCH_ERROR);

        $this->httpExceptionListener->attach($eventManager);
    }

    public function testReturnIfNoException()
    {
        $this->assertNull($this->httpExceptionListener->onDispatchError($this->event));
    }

    public function testPopulateResponse()
    {
        $exception = $this->getMock('ZfrRest\Http\Exception\HttpExceptionInterface');
        $this->event->setParam('exception', $exception);

        $exception->expects($this->once())
                  ->method('prepareResponse')
                  ->with($this->isInstanceOf('Zend\Http\Response'));

        $exception->expects($this->once())->method('getErrors')->will($this->returnValue(['email' => 'invalid']));

        $this->httpExceptionListener->onDispatchError($this->event);

        $response = $this->event->getResponse();

        $this->assertNotSame($this->response, $response, 'Assert response is replaced');
        $this->assertInstanceOf('Zend\Http\Response', $this->event->getResponse());
        $this->assertInstanceOf('Zend\Http\Response', $this->event->getResult());
        $this->assertEquals(['errors' => ['email' => 'invalid']], json_decode($this->event->getResponse()->getContent(), true));
    }

    public function testCanCreateFromCustomException()
    {
        $httpExceptionListener = new HttpExceptionListener([
            'InvalidArgumentException' => 'ZfrRest\Http\Exception\Client\NotFoundException'
        ]);

        $this->event->setParam('exception', new \InvalidArgumentException('An error'));

        $httpExceptionListener->onDispatchError($this->event);

        $this->assertInstanceOf('Zend\Http\Response', $this->event->getResponse());
        $this->assertEquals('An error', $this->event->getResponse()->getReasonPhrase());
    }
}
