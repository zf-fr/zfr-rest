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

namespace ZfrRestTest\Mvc\Controller\Plugin;

use PHPUnit_Framework_TestCase;
use Zend\Http\Request as HttpRequest;
use Zend\Http\Response as HttpResponse;
use Zend\InputFilter\BaseInputFilter;
use Zend\InputFilter\InputFilterPluginManager;
use Zend\Mvc\Controller\PluginManager;
use Zend\Mvc\MvcEvent;
use Zend\ServiceManager\ServiceManager;
use Zend\Stdlib\Parameters;
use Zend\Stdlib\RequestInterface;
use ZfrRest\Http\Exception;
use ZfrRest\Mvc\Controller\AbstractRestfulController;
use ZfrRest\Mvc\Controller\Plugin\ValidateIncomingData;
use ZfrRestTest\Asset\Controller\SimpleController;
use ZfrRestTest\Asset\InputFilter\SimpleInputFilter;

/**
 * @licence MIT
 * @author  Florent Blaison <florent.blaison@gmail.com>
 *
 * @group   Coverage
 * @covers  \ZfrRest\Mvc\Controller\Plugin\ValidateIncomingData
 */
class ValidateIncomingDataTest extends PHPUnit_Framework_TestCase
{

    public function testValidateIncomingData()
    {
        $inputFilter = new SimpleInputFilter();

        $inputFilterPluginManager = $this->getMock(InputFilterPluginManager::class, [], [new ServiceManager()]);
        $inputFilterPluginManager->expects($this->any())
                                 ->method('get')
                                 ->with(SimpleInputFilter::class)
                                 ->willReturn($inputFilter);

        $request = new HttpRequest();
        $request->setPost(
            new Parameters(
                [
                    'fields1' => 'value1',
                    'fields2' => 'value2'
                ]
            )
        );
        $request->setContent(json_encode(['fields3' => 'value3']));

        $controller = $this->getMock(AbstractRestfulController::class);
        $controller->expects($this->at(0))
                   ->method('getRequest')
                   ->willReturn($request);

        $controller->expects($this->at(1))
                   ->method('getRequest')
                   ->willReturn($request);

        $plugin = new ValidateIncomingData($inputFilterPluginManager);
        $plugin->setController($controller);

        $expected = [
            'fields1' => 'value1',
            'fields2' => 'value2',
            'fields3' => 'value3',
        ];

        $result = $plugin(SimpleInputFilter::class);

        $this->assertEquals($expected, $result);
    }
}
