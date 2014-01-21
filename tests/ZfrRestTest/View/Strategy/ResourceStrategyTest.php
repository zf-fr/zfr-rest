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

namespace ZfrRestTest\View\Strategy;

use PHPUnit_Framework_TestCase;
use Zend\Http\Response as HttpResponse;
use Zend\View\ViewEvent;
use ZfrRest\View\Strategy\ResourceStrategy;

/**
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group Coverage
 * @covers \ZfrRest\View\Strategy\ResourceStrategy
 */
class ResourceStrategyTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var ResourceStrategy
     */
    protected $resourceStrategy;

    /**
     * @var \ZfrRest\View\Renderer\ResourceRendererInterface
     */
    protected $renderer;

    /**
     * Set up
     */
    public function setUp()
    {
        parent::setUp();

        $this->renderer         = $this->getMock('ZfrRest\View\Renderer\ResourceRendererInterface');
        $this->resourceStrategy = new ResourceStrategy($this->renderer);
    }

    public function testAttachToCorrectEvent()
    {
        $eventManager = $this->getMock('Zend\EventManager\EventManagerInterface');
        $eventManager->expects($this->at(0))->method('attach')->with(ViewEvent::EVENT_RENDERER);
        $eventManager->expects($this->at(1))->method('attach')->with(ViewEvent::EVENT_RESPONSE);

        $this->resourceStrategy->attach($eventManager);
    }

    public function testCanSelectRenderer()
    {
        // If not a ResourceModel, should return null
        $viewEvent = new ViewEvent();
        $viewEvent->setModel($this->getMock('Zend\View\Model\ModelInterface'));

        $this->assertNull($this->resourceStrategy->selectRenderer($viewEvent));

        // If a ResourceModel, should return the renderer
        $viewEvent->setModel($this->getMock('ZfrRest\View\Model\ResourceModel', [], [], '', false));
        $this->assertSame($this->renderer, $this->resourceStrategy->selectRenderer($viewEvent));
    }

    public function testShouldReturnNullIfNotSameRenderer()
    {
        $viewEvent = new ViewEvent();
        $viewEvent->setRenderer($this->getMock('Zend\View\Renderer\RendererInterface'));

        $this->assertNull($this->resourceStrategy->injectResponse($viewEvent));
    }

    public function testDoNothingIfResultIsNotAString()
    {
        $viewEvent = new ViewEvent();
        $viewEvent->setRenderer($this->renderer);
        $viewEvent->setResponse(new HttpResponse());

        $this->assertNull($this->resourceStrategy->injectResponse($viewEvent));
    }

    public function testPopulateResponse()
    {
        $viewEvent = new ViewEvent();
        $viewEvent->setRenderer($this->renderer);

        $response = new HttpResponse();
        $viewEvent->setResponse($response);

        $viewEvent->setResult(json_encode(['foo' => 'bar']));

        $this->resourceStrategy->injectResponse($viewEvent);

        $this->assertTrue($response->getHeaders()->has('Content-Type'));
        $this->assertEquals(['foo' => 'bar'], json_decode($response->getContent(), true));
    }
}
