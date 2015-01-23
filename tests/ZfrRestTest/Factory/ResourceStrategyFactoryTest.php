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

namespace ZfrRestTest\Factory;

use PHPUnit_Framework_TestCase;
use Zend\Http\Response as HttpResponse;
use Zend\ServiceManager\ServiceLocatorInterface;
use ZfrRest\Factory\ResourceStrategyFactory;
use ZfrRest\View\Renderer\ResourceRenderer;
use ZfrRest\View\Strategy\ResourceStrategy;

/**
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group Coverage
 * @covers \ZfrRest\Factory\ResourceStrategyFactory
 */
class ResourceStrategyFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testFactory()
    {
        $serviceLocator = $this->getMock(ServiceLocatorInterface::class);

        $serviceLocator->expects($this->once())
                       ->method('get')
                       ->with(ResourceRenderer::class)
                       ->will($this->returnValue($this->getMock(ResourceRenderer::class, [], [], '', false)));

        $factory  = new ResourceStrategyFactory();
        $instance = $factory->createService($serviceLocator);

        $this->assertInstanceOf(ResourceStrategy::class, $instance);
    }
}
