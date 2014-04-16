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
        $inputFilterManager = $this->getMock('Zend\ServiceManager\AbstractPluginManager');
        $event              = new ValidationEvent($resource, $inputFilterManager);

        $this->assertAttributeEquals($resource, 'resource', $event);
        $this->assertAttributeEquals($inputFilterManager, 'inputFilterManager', $event);
    }

    public function testSetGetAutoValidate()
    {
        $resource           = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $inputFilterManager = $this->getMock('Zend\ServiceManager\AbstractPluginManager');
        $event              = new ValidationEvent($resource, $inputFilterManager);

        $this->assertTrue($event->getAutoValidate());

        $event->setAutoValidate(0);

        $this->assertFalse($event->getAutoValidate());
    }

    public function testSetGetInputFilter()
    {
        $resource           = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $inputFilterManager = $this->getMock('Zend\ServiceManager\AbstractPluginManager');
        $inputFilter        = $this->getMock('Zend\InputFilter\InputFilterInterface');
        $event              = new ValidationEvent($resource, $inputFilterManager);

        $this->assertNull($event->getInputFilter());

        $event->setInputFilter($inputFilter);

        $this->assertSame($inputFilter, $event->getInputFilter());
    }

    public function testSetGetResource()
    {
        $resourceA          = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $resourceB          = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $inputFilterManager = $this->getMock('Zend\ServiceManager\AbstractPluginManager');
        $event              = new ValidationEvent($resourceA, $inputFilterManager);

        $this->assertSame($resourceA, $event->getResource());

        $event->setResource($resourceB);

        $this->assertSame($resourceB, $event->getResource());
    }

    public function testSetGetInputFilterManager()
    {
        $resource            = $this->getMock('ZfrRest\Resource\ResourceInterface');
        $inputFilterManagerA = $this->getMock('Zend\ServiceManager\AbstractPluginManager');
        $inputFilterManagerB = $this->getMock('Zend\ServiceManager\AbstractPluginManager');
        $event               = new ValidationEvent($resource, $inputFilterManagerA);

        $this->assertSame($inputFilterManagerA, $event->getInputFilterManager());

        $event->setInputFilterManager($inputFilterManagerB);

        $this->assertSame($inputFilterManagerB, $event->getInputFilterManager());
    }
}
