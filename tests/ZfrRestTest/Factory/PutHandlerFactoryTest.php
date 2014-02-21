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
use Zend\InputFilter\InputFilterPluginManager;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Hydrator\HydratorPluginManager;
use ZfrRest\Factory\PutHandlerFactory;
use ZfrRest\Mvc\Controller\MethodHandler\MethodHandlerPluginManager;

/**
 * @licence MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group Coverage
 * @covers \ZfrRest\Factory\PutHandlerFactory
 */
class PutHandlerFactoryTest extends PHPUnit_Framework_TestCase
{
    public function testCreateFromFactory()
    {
        $serviceManager = new ServiceManager();

        $pluginManager  = new MethodHandlerPluginManager();
        $pluginManager->setServiceLocator($serviceManager);

        $serviceManager->setService('InputFilterManager', new InputFilterPluginManager());
        $serviceManager->setService('HydratorManager', new HydratorPluginManager());

        $factory = new PutHandlerFactory();
        $result  = $factory->createService($pluginManager);

        $this->assertInstanceOf('ZfrRest\Mvc\Controller\MethodHandler\PutHandler', $result);
    }
}
