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
use ZfrRest\Mvc\Controller\Event\ValidationEvent;
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

    public function setUp()
    {
        $this->resource           = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $this->controller         = $this->getMock('Zend\EventManager\EventManagerAwareInterface');
        $this->eventManager       = $this->getMock('Zend\EventManager\EventManagerInterface');
        $this->inputFilterManager = $this->getMock('Zend\InputFilter\InputFilterPluginManager');
        $this->dataValidation     = new DataValidationObject($this->inputFilterManager);

        $this->controller->expects($this->once())->method('getEventManager')->will($this->returnValue($this->eventManager));
    }

    public function testThrowExceptionIfNoInputFilterNameIsDefined()
    {
        $this->setExpectedException('ZfrRest\Mvc\Exception\RuntimeException');

        $metadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');

        $this->resource->expects($this->once())->method('getMetadata')->will($this->returnValue($metadata));
        $metadata->expects($this->once())->method('getInputFilterName')->will($this->returnValue(null));

        $this->dataValidation->validateData($this->resource, [], $this->controller);
    }

    public function testTriggerEventAndSkipIfAutoValidationDisabled()
    {
        $rawData = ['foo' => 'bar'];

        $callback = function ($event) {
            return ($event instanceof ValidationEvent)
                && ($event->getTarget() === $this->controller)
                && ($event->getResource() === $this->resource)
                && ($event->getInputFilterManager() === $this->inputFilterManager);
        };

        $callback->bindTo($this);

        $this->eventManager->expects($this->once())->method('trigger')->with(
            $this->equalTo(ValidationEvent::EVENT_VALIDATE_PRE),
            $this->callback($callback)
        )->will($this->returnCallback(function ($name, $event) {
            // Disable auto validation
            $event->setAutoValidate(false);
        }));

        $result = $this->dataValidation->validateData($this->resource, $rawData, $this->controller);

        $this->assertSame($rawData, $result);
    }

    public function testValidateWithCustomInputFilter()
    {
        $rawData           = ['foo' => 'bar'];
        $validData         = ['foo' => 'baz'];
        $customInputFilter = $this->getMock('Zend\InputFilter\InputFilterInterface');

        $this->eventManager->expects($this->at(0))->method('trigger')->with($this->equalTo(ValidationEvent::EVENT_VALIDATE_PRE))->will(
            $this->returnCallback(function ($name, $event) use ($customInputFilter) {
                // Set custom InputFilter
                $event->setInputFilter($customInputFilter);
            })
        );

        $customInputFilter->expects($this->once())->method('setData')->with($this->equalTo($rawData));
        $customInputFilter->expects($this->once())->method('isValid')->will($this->returnValue(true));
        $customInputFilter->expects($this->once())->method('getValues')->will($this->returnValue($validData));

        $this->eventManager->expects($this->at(1))->method('trigger')->with($this->equalTo(ValidationEvent::EVENT_VALIDATE_SUCCESS));

        $result = $this->dataValidation->validateData($this->resource, $rawData, $this->controller);

        $this->assertSame($validData, $result);
    }

    public function testValidateWithDefaultInputFilter()
    {
        $metadata    = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');
        $inputFilter = $this->getMock('Zend\InputFilter\InputFilterInterface');
        $rawData     = ['foo' => 'bar'];
        $validData   = ['foo' => 'baz'];
        $context     = new \stdClass();

        $this->resource->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($context));

        $this->resource->expects($this->once())
            ->method('getMetadata')
            ->will($this->returnValue($metadata));

        $metadata->expects($this->once())
            ->method('getInputFilterName')
            ->will($this->returnValue('FooInputFilter'));

        $this->inputFilterManager->expects($this->once())
            ->method('get')
            ->with($this->equalTo('FooInputFilter'))
            ->will($this->returnValue($inputFilter));

        $inputFilter->expects($this->once())->method('setData')->with($this->equalTo($rawData));
        $inputFilter->expects($this->once())->method('isValid')->with($this->equalTo($context))->will($this->returnValue(true));
        $inputFilter->expects($this->once())->method('getValues')->will($this->returnValue($validData));

        $this->eventManager->expects($this->at(1))->method('trigger')->with(
            $this->equalTo(ValidationEvent::EVENT_VALIDATE_SUCCESS),
            $this->callback(function ($event) use ($inputFilter) {
                return ($event instanceof ValidationEvent && $event->getInputFilter() === $inputFilter);
            })
        );

        $result = $this->dataValidation->validateData($this->resource, $rawData, $this->controller);

        $this->assertSame($validData, $result);
    }

    public function testThrowExceptionOnFailedValidation()
    {
        $this->setExpectedException('ZfrRest\Http\Exception\Client\UnprocessableEntityException');

        $inputFilter = new InputFilter();
        $inputFilter->add([
            'name'     => 'email',
            'required' => true
        ]);

        $this->eventManager->expects($this->at(0))->method('trigger')->with($this->equalTo(ValidationEvent::EVENT_VALIDATE_PRE))->will(
            $this->returnCallback(function ($name, $event) use ($inputFilter) {
                // Set InputFilter
                $event->setInputFilter($inputFilter);
            })
        );

        $this->eventManager->expects($this->at(1))->method('trigger')->with($this->equalTo(ValidationEvent::EVENT_VALIDATE_ERROR));

        $errorMessages = ['email' => ['Value is required and can\'t be empty']];

        try {
            $this->dataValidation->validateData($this->resource, ['foo'], $this->controller);
        } catch (UnprocessableEntityException $exception) {
            $this->assertEquals($errorMessages, $exception->getErrors());

            throw $exception;
        }
    }

    public function testThrowExceptionOnFailedValidationWithNestedInputFilter()
    {
        $this->setExpectedException('ZfrRest\Http\Exception\Client\UnprocessableEntityException');

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

        $this->eventManager->expects($this->at(0))->method('trigger')->with($this->equalTo(ValidationEvent::EVENT_VALIDATE_PRE))->will(
            $this->returnCallback(function ($name, $event) use ($inputFilter) {
                // Set InputFilter
                $event->setInputFilter($inputFilter);
            })
        );

        $this->eventManager->expects($this->at(1))->method('trigger')->with($this->equalTo(ValidationEvent::EVENT_VALIDATE_ERROR));

        $errorMessages = [
            'email'   => ['Value is required and can\'t be empty'],
            'address' => [
                'city' => ['Value is required and can\'t be empty']
            ]
        ];

        try {
            $this->dataValidation->validateData($this->resource, ['foo'], $this->controller);
        } catch (UnprocessableEntityException $exception) {
            $this->assertEquals($errorMessages, $exception->getErrors());

            throw $exception;
        }
    }
}
