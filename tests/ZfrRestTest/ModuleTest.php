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

namespace ZfrRestTest;

use PHPUnit_Framework_TestCase;
use Zend\Mvc\Application;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use ZfrRest\Module;
use ZfrRest\Options\ModuleOptions;

/**
 * Tests for {@see \ZfrRest\Module}
 *
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 *
 * @group Coverage
 */
class ModuleTest extends PHPUnit_Framework_TestCase
{
    /**
     * @covers \ZfrRest\Module::getConfig
     */
    public function testGetConfig()
    {
        $module = new Module();

        $this->assertInternalType('array', $module->getConfig());
        $this->assertSame($module->getConfig(), unserialize(serialize($module->getConfig())), 'Config is serializable');
    }

    /**
     * @covers \ZfrRest\Module::onBootstrap
     */
    public function testAssertListenersAreCorrectlyRegistered()
    {
        $module         = new Module();
        $mvcEvent       = $this->getMock('Zend\Mvc\MvcEvent');
        $application    = $this->getMock('Zend\Mvc\Application', array(), array(), '', false);
        $eventManager   = $this->getMock('Zend\EventManager\EventManagerInterface');
        $serviceManager = $this->getMock('Zend\ServiceManager\ServiceManager');

        $httpOverrideListener = $this->getMock('ZfrRest\Mvc\HttpMethodOverrideListener', array(), array(), '', false);

        $moduleOptions = new ModuleOptions();
        $moduleOptions->setRegisterHttpMethodOverrideListener(true);

        $serviceManager->expects($this->at(0))
                       ->method('get')
                       ->with('ZfrRest\Options\ModuleOptions')
                       ->will($this->returnValue($moduleOptions));

        $mvcEvent->expects($this->any())->method('getTarget')->will($this->returnValue($application));
        $application->expects($this->any())->method('getEventManager')->will($this->returnValue($eventManager));
        $application->expects($this->any())->method('getServiceManager')->will($this->returnValue($serviceManager));
        $serviceManager->expects($this->at(1))
                       ->method('get')
                       ->with('ZfrRest\Mvc\HttpMethodOverrideListener')
                       ->will($this->returnValue($httpOverrideListener));

        $eventManager->expects($this->once())->method('attach')->with($httpOverrideListener);

        $module->onBootstrap($mvcEvent);
    }
}
