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

namespace ZfrRestTest\Mvc\Controller\MethodHandler;

use PHPUnit_Framework_TestCase;
use Zend\InputFilter\InputFilter;
use ZfrRest\Http\Exception\Client\UnprocessableEntityException;
use ZfrRestTest\Asset\Mvc\DataValidationObject;

/**
 * @licence MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group  Coverage
 * @covers \ZfrRest\Mvc\Controller\MethodHandler\DataValidationTrait
 */
class DataValidationTraitTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var DataValidationObject
     */
    protected $dataValidation;

    /**
     * @var \Zend\InputFilter\InputFilterPluginManager
     */
    protected $inputFilterPluginManager;

    public function setUp()
    {
        $this->inputFilterPluginManager = $this->getMock('Zend\InputFilter\InputFilterPluginManager');
        $this->dataValidation           = new DataValidationObject($this->inputFilterPluginManager);
    }

    public function testThrowExceptionIfNoInputFilterNameIsDefined()
    {
        $this->setExpectedException('ZfrRest\Mvc\Exception\RuntimeException');

        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $metadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');

        $resource->expects($this->once())->method('getMetadata')->will($this->returnValue($metadata));
        $metadata->expects($this->once())->method('getInputFilterName')->will($this->returnValue(null));

        $controller = $this->getMock('ZfrRest\Mvc\Controller\AbstractRestfulController');
        $controller->expects($this->never())->method('configureInputFilter');

        $this->dataValidation->validateData($resource, [], $controller);
    }

    public function testCanValidateData()
    {
        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $metadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');
        $context  = new \stdClass;

        $resource->expects($this->once())->method('getData')->will($this->returnValue($context));
        $resource->expects($this->once())->method('getMetadata')->will($this->returnValue($metadata));
        $metadata->expects($this->once())->method('getInputFilterName')->will($this->returnValue('inputFilter'));

        $data = ['foo'];

        $inputFilter = $this->getMock('Zend\InputFilter\InputFilterInterface');
        $inputFilter->expects($this->once())->method('setData')->with($data);
        $inputFilter->expects($this->once())
                    ->method('isValid')
                    ->with($context)
                    ->will($this->returnValue(true));

        $inputFilter->expects($this->once())
                    ->method('getValues')
                    ->will($this->returnValue(['filtered']));

        $controller = $this->getMock('ZfrRest\Mvc\Controller\AbstractRestfulController');
        $controller->expects($this->once())
                   ->method('getInputFilter')
                   ->with($this->inputFilterPluginManager, 'inputFilter')
                   ->will($this->returnValue($inputFilter));

        $result = $this->dataValidation->validateData($resource, $data, $controller);

        $this->assertEquals(['filtered'], $result);
    }

    public function testThrowExceptionOnFailedValidation()
    {
        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $metadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');

        $resource->expects($this->once())->method('getMetadata')->will($this->returnValue($metadata));
        $metadata->expects($this->once())->method('getInputFilterName')->will($this->returnValue('inputFilter'));

        $data = ['foo'];

        $inputFilter = new InputFilter();
        $inputFilter->add([
            'name'     => 'email',
            'required' => true
        ]);

        $controller = $this->getMock('ZfrRest\Mvc\Controller\AbstractRestfulController');
        $controller->expects($this->once())
                   ->method('getInputFilter')
                   ->with($this->inputFilterPluginManager, 'inputFilter')
                   ->will($this->returnValue($inputFilter));

        $exceptionTriggered = false;
        $errorMessages      = ['email' => ['Value is required and can\'t be empty']];

        try {
            $this->dataValidation->validateData($resource, $data, $controller);
        } catch(UnprocessableEntityException $exception) {
            $exceptionTriggered = true;
            $this->assertEquals($errorMessages, $exception->getErrors());
        }

        $this->assertTrue($exceptionTriggered);
    }

    public function testThrowExceptionOnFailedValidationWithNestedInputFilter()
    {
        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $metadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');

        $resource->expects($this->once())->method('getMetadata')->will($this->returnValue($metadata));
        $metadata->expects($this->once())->method('getInputFilterName')->will($this->returnValue('inputFilter'));

        $data = ['foo'];

        $inputFilter = new InputFilter();
        $inputFilter->add([
            'name'     => 'email',
            'required' => true
        ]);

        $inputFilter->add([
            'type' => 'Zend\InputFilter\InputFilter',
            'city' => [
                'name' => 'address'
            ]
        ], 'address');

        $controller = $this->getMock('ZfrRest\Mvc\Controller\AbstractRestfulController');
        $controller->expects($this->once())
            ->method('getInputFilter')
            ->with($this->inputFilterPluginManager, 'inputFilter')
            ->will($this->returnValue($inputFilter));

        $exceptionTriggered = false;
        $errorMessages      = [
            'email'   => ['Value is required and can\'t be empty'],
            'address' => [
                'city' => ['Value is required and can\'t be empty']
            ]
        ];

        try {
            $this->dataValidation->validateData($resource, $data, $controller);
        } catch(UnprocessableEntityException $exception) {
            $exceptionTriggered = true;
            $this->assertEquals($errorMessages, $exception->getErrors());
        }

        $this->assertTrue($exceptionTriggered);
    }
}
