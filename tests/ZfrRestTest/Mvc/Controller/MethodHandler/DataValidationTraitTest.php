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
use ZfrRest\Options\ControllerBehavioursOptions;
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

        $this->dataValidation->validateData($resource, []);
    }

    public function testCanValidateData()
    {
        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $metadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');

        $resource->expects($this->once())->method('getMetadata')->will($this->returnValue($metadata));
        $metadata->expects($this->once())->method('getInputFilterName')->will($this->returnValue('inputFilter'));

        $data = ['foo'];

        $inputFilter = $this->getMock('Zend\InputFilter\InputFilterInterface');
        $inputFilter->expects($this->once())->method('setData')->with($data);
        $inputFilter->expects($this->once())
                    ->method('isValid')
                    ->will($this->returnValue(true));

        $this->inputFilterPluginManager->expects($this->once())
                                       ->method('get')
                                       ->with('inputFilter')
                                       ->will($this->returnValue($inputFilter));

        $inputFilter->expects($this->once())
                    ->method('getValues')
                    ->will($this->returnValue(['filtered']));

        $result = $this->dataValidation->validateData($resource, $data);

        $this->assertEquals(['filtered'], $result);
    }

    public function testThrowExceptionOnFailedValidation()
    {
        $this->setExpectedException('ZfrRest\Http\Exception\Client\BadRequestException');

        $resource = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $metadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');

        $resource->expects($this->once())->method('getMetadata')->will($this->returnValue($metadata));
        $metadata->expects($this->once())->method('getInputFilterName')->will($this->returnValue('inputFilter'));

        $data          = ['foo'];
        $errorMessages = ['email' => ['Email is invalid']];

        $inputFilter = $this->getMock('Zend\InputFilter\InputFilterInterface');
        $inputFilter->expects($this->once())->method('setData')->with($data);
        $inputFilter->expects($this->once())->method('getMessages')->will($this->returnValue($errorMessages));
        $inputFilter->expects($this->once())
                    ->method('isValid')
                    ->will($this->returnValue(false));

        $this->inputFilterPluginManager->expects($this->once())
                                       ->method('get')
                                       ->with('inputFilter')
                                       ->will($this->returnValue($inputFilter));

        $this->dataValidation->validateData($resource, $data);
    }
}
