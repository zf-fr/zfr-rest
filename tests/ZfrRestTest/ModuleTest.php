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
use ZfrRest\Module;

/**
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group Coverage
 * @covers \ZfrRest\Module
 */
class ModuleTest extends PHPUnit_Framework_TestCase
{
    public function testGetConfig()
    {
        $module = new Module();

        $this->assertInternalType('array', $module->getConfig());
        $this->assertSame($module->getConfig(), unserialize(serialize($module->getConfig())), 'Config is serializable');
    }

    public function testDependency()
    {
        $module = new Module();
        $this->assertEquals(['DoctrineModule'], $module->getModuleDependencies());
    }

    public function testListenersAreRegistered()
    {
        $event = $this->getEvent();

        $module = new Module();
        $module->onBootstrap($event);
    }

    /**
     * @return \Zend\EventManager\EventInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getEvent()
    {
        $serviceLocator = $this->getMock('Zend\ServiceManager\ServiceLocatorInterface');
        $eventManager   = $this->getMock('Zend\EventManager\EventManagerInterface');

        $application = $this->getMock('Zend\Mvc\Application', [], [], '', false);
        $application->expects($this->any())->method('getServiceManager')->will($this->returnValue($serviceLocator));
        $application->expects($this->any())->method('getEventManager')->will($this->returnValue($eventManager));

        $event = $this->getMock('Zend\EventManager\EventInterface');
        $event->expects($this->any())->method('getTarget')->will($this->returnValue($application));

        return $event;
    }
}
