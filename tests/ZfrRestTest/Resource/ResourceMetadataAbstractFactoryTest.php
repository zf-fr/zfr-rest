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

namespace ZfrRestTest\Resource;

use PHPUnit_Framework_TestCase as TestCase;
use ZfrRest\Resource\ResourceMetadataAbstractFactory;

/**
 * Tests for {@see \ZfrRest\Resource\ResourceMetadataAbstractFactory}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class ResourceMetadataAbstractFactoryTest extends TestCase
{
    /**
     * @var \Doctrine\Common\Persistence\Mapping\ClassMetadataFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $classMetadataFactory;

    /**
     * @var \ZfrRest\Resource\ResourceMetadataAbstractFactory
     */
    protected $factory;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var \Zend\ServiceManager\ServiceLocatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $serviceLocator;

    /**
     * {@inheritDoc}
     *
     * @covers \ZfrRest\Resource\ResourceMetadataAbstractFactory::__construct
     */
    public function setUp()
    {
        $this->config = array(
            'Test' => array(
                'controller'   => 'TestController',
                'input_filter' => 'TestInputFilter',
                'hydrator'     => 'TestHydrator',
                'encoders'     => array(
                    'test-encoder' => 'TestEncoder',
                ),
                'decoders'     => array(
                    'test-decoder' => 'TestDecoder',
                ),
                'associations' => array(
                    'associationName',
                ),
            ),
        );

        $this->serviceLocator       = $this->getMock('Zend\\ServiceManager\\ServiceLocatorInterface');
        $this->classMetadataFactory = $this->getMock('Doctrine\\Common\\Persistence\\Mapping\\ClassMetadataFactory');
        $this->factory              = new ResourceMetadataAbstractFactory($this->classMetadataFactory, $this->config);
    }

    /**
     * @covers \ZfrRest\Resource\ResourceMetadataAbstractFactory::createServiceWithName
     * @covers \ZfrRest\Resource\ResourceMetadataAbstractFactory::canCreateServiceWithName
     */
    public function testCreateServiceWithName()
    {
        $classMetadata  = $this->getMock('Doctrine\\Common\\Persistence\\Mapping\\ClassMetadata');

        $this
            ->classMetadataFactory
            ->expects($this->any())
            ->method('isTransient')
            ->with('Test')
            ->will($this->returnValue(false));
        $this
            ->classMetadataFactory
            ->expects($this->any())
            ->method('getMetadataFor')
            ->with('Test')
            ->will($this->returnValue($classMetadata));

        $this->assertTrue($this->factory->canCreateServiceWithName($this->serviceLocator, 'test', 'Test'));
        $metadata = $this->factory->createServiceWithName($this->serviceLocator, 'test', 'Test');

        $this->assertSame($classMetadata, $metadata->getClassMetadata());
        $this->assertSame('TestController', $metadata->getControllerName());
        $this->assertSame('TestInputFilter', $metadata->getInputFilterName());
        $this->assertSame('TestHydrator', $metadata->getHydratorName());
        $this->assertSame(array('test-encoder' => 'TestEncoder'), $metadata->getEncoderNames());
        $this->assertSame(array('test-decoder' => 'TestDecoder'), $metadata->getDecoderNames());
        $this->assertSame(array('associationName'), $metadata->getAssociations());
    }

    /**
     * @covers \ZfrRest\Resource\ResourceMetadataAbstractFactory::createServiceWithName
     * @covers \ZfrRest\Resource\ResourceMetadataAbstractFactory::canCreateServiceWithName
     */
    public function testCannotCreateServiceWithoutConfiguration()
    {
        $this
            ->classMetadataFactory
            ->expects($this->any())
            ->method('isTransient')
            ->with('NonExisting')
            ->will($this->returnValue(false));

        $this->assertFalse(
            $this->factory->canCreateServiceWithName($this->serviceLocator, 'nonexisting', 'NonExisting')
        );

        $this->setExpectedException('Zend\\ServiceManager\\Exception\\ServiceNotFoundException');
        $this->factory->createServiceWithName($this->serviceLocator, 'nonexisting', 'NonExisting');
    }

    /**
     * @covers \ZfrRest\Resource\ResourceMetadataAbstractFactory::createServiceWithName
     * @covers \ZfrRest\Resource\ResourceMetadataAbstractFactory::canCreateServiceWithName
     */
    public function testCannotCreateServiceWithoutMetadata()
    {
        $this
            ->classMetadataFactory
            ->expects($this->any())
            ->method('isTransient')
            ->with('Test')
            ->will($this->returnValue(true));

        $this->assertFalse(
            $this->factory->canCreateServiceWithName($this->serviceLocator, 'test', 'Test')
        );

        $this->setExpectedException('Zend\\ServiceManager\\Exception\\ServiceNotFoundException');
        $this->factory->createServiceWithName($this->serviceLocator, 'test', 'Test');
    }
}