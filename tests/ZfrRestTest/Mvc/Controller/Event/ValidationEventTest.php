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

namespace ZfrRestTest\Mvc\Controller\Event;

use PHPUnit_Framework_TestCase as TestCase;
use ZfrRest\Mvc\Controller\Event\ValidationEvent;

/**
 * @licence MIT
 * @author  Daniel Gimenes <daniel@danielgimenes.com.br>
 *
 * @group  Coverage
 * @covers \ZfrRest\Mvc\Controller\Event\ValidationEvent
 */
class ValidationEventTest extends TestCase
{
    public function testConstructorStoreParameters()
    {
        $resource           = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $inputFilterManager = $this->getMock('Zend\InputFilter\InputFilterPluginManager');
        $event              = new ValidationEvent($resource, $inputFilterManager);

        $this->assertAttributeEquals($resource, 'resource', $event);
        $this->assertAttributeEquals($inputFilterManager, 'inputFilterManager', $event);
    }

    public function testSetGetAutoValidate()
    {
        $resource           = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $inputFilterManager = $this->getMock('Zend\InputFilter\InputFilterPluginManager');
        $event              = new ValidationEvent($resource, $inputFilterManager);

        $this->assertTrue($event->getAutoValidate());

        $event->setAutoValidate(0);

        $this->assertFalse($event->getAutoValidate());
    }

    public function testSetGetInputFilter()
    {
        $resource           = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $metadata           = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');
        $inputFilterManager = $this->getMock('Zend\InputFilter\InputFilterPluginManager');
        $inputFilter        = $this->getMock('Zend\InputFilter\InputFilterInterface');
        $event              = new ValidationEvent($resource, $inputFilterManager);

        $resource->expects($this->once())->method('getMetadata')->will($this->returnValue($metadata));
        $metadata->expects($this->once())->method('getInputFilterName')->will($this->returnValue('MyInputFilter'));

        $expectedInputfilter = $this->getMock('Zend\InputFilter\InputFilterInterface');
        $inputFilterManager->expects($this->once())
            ->method('get')
            ->with('MyInputFilter')
            ->will($this->returnValue($expectedInputfilter));

        $this->assertSame($expectedInputfilter, $event->getInputFilter());

        $event->setInputFilter($inputFilter);

        $this->assertSame($inputFilter, $event->getInputFilter());
    }

    public function testGetResource()
    {
        $resource           = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $inputFilterManager = $this->getMock('Zend\InputFilter\InputFilterPluginManager');
        $event              = new ValidationEvent($resource, $inputFilterManager);

        $this->assertSame($resource, $event->getResource());
    }

    public function testGetInputFilterManager()
    {
        $resource           = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $inputFilterManager = $this->getMock('Zend\InputFilter\InputFilterPluginManager');
        $event              = new ValidationEvent($resource, $inputFilterManager);

        $this->assertSame($inputFilterManager, $event->getInputFilterManager());
    }
}
