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
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Request as HttpRequest;
use Zend\Http\Response as HttpResponse;
use Zend\Http\Response;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ResponseInterface;
use ZfrRest\Http\Exception;
use ZfrRest\Mvc\HttpExceptionListener;
use ZfrRest\Mvc\ResourceResponseListener;

/**
 * @licence MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group Coverage
 * @covers \ZfrRest\Mvc\ResourceResponseListener
 */
class ResourceResponseListenerTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ResourceResponseListener
     */
    protected $resourceResponseListener;

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

        $this->resourceResponseListener = new ResourceResponseListener();

        // Init the MvcEvent object
        $this->response = new HttpResponse();

        $this->event = new MvcEvent();
        $this->event->setResponse($this->response);
    }

    public function testAttachToCorrectEvent()
    {
        $eventManager = $this->getMock(EventManagerInterface::class);
        $eventManager->expects($this->once())->method('attach')->with(MvcEvent::EVENT_FINISH);

        $this->resourceResponseListener->attach($eventManager);
    }

    public function testReturnIfNotHttpResponse()
    {
        $response = $this->getMock(ResponseInterface::class);
        $response->expects($this->never())->method('setStatusCode');

        $this->event->setResponse($response);

        $this->assertNull($this->resourceResponseListener->finishResponse($this->event));
    }

    public function testSet204StatusCodeIfDeleteAndNoBody()
    {
        $request = new HttpRequest();
        $request->setMethod(HttpRequest::METHOD_DELETE);

        $this->event->setRequest($request);

        $this->response->setContent('');

        $this->resourceResponseListener->finishResponse($this->event);

        $this->assertEquals(204, $this->response->getStatusCode());
    }

    public function testDoesNotChangeStatusIfDeleteHasBody()
    {
        $request = new HttpRequest();
        $request->setMethod(HttpRequest::METHOD_DELETE);
        $this->event->setRequest($request);

        $this->response->setContent('foo');

        $this->resourceResponseListener->finishResponse($this->event);

        $this->assertEquals(200, $this->response->getStatusCode());
    }

    public function testSet201StatusCodeIfPost()
    {
        $request = new HttpRequest();
        $request->setMethod(HttpRequest::METHOD_POST);

        $this->event->setRequest($request);

        $this->response->setContent('foo');

        $this->resourceResponseListener->finishResponse($this->event);

        $this->assertEquals(201, $this->response->getStatusCode());
    }
}
