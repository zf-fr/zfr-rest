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

namespace ZfrRestTest\Resource\Metadata;

use PHPUnit_Framework_TestCase as TestCase;
use Zend\Server\Reflection\ReflectionClass;
use ZfrRest\Resource\Metadata\ResourceMetadata;

/**
 * Tests for {@see \ZfrRest\Resource\Metadata\ResourceMetadata}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class ResourceMetadataTest extends TestCase
{
    /**
     * @covers \ZfrRest\Resource\Metadata\ResourceMetadata
     */
    public function testResourceMetadata()
    {
        $resourceMetadata = new ResourceMetadata('stdClass');
        $this->assertSame('stdClass', $resourceMetadata->getClassName());

        $metadata                        = $this->getMock('Doctrine\\Common\\Persistence\\Mapping\\ClassMetadata');
        $resourceMetadata->classMetadata = $metadata;
        $this->assertSame($metadata, $resourceMetadata->getClassMetadata());

        $resourceMetadata->controller = 'test';
        $this->assertSame('test', $resourceMetadata->getControllerName());
        $resourceMetadata->controller = null;
        $this->assertSame(null, $resourceMetadata->getControllerName());

        $resourceMetadata->inputFilter = 'test';
        $this->assertSame('test', $resourceMetadata->getInputFilterName());
        $resourceMetadata->inputFilter = null;
        $this->assertSame(null, $resourceMetadata->getInputFilterName());

        $resourceMetadata->hydrator = 'test';
        $this->assertSame('test', $resourceMetadata->getHydratorName());
        $resourceMetadata->hydrator = null;
        $this->assertSame(null, $resourceMetadata->getHydratorName());

        $this->assertFalse($resourceMetadata->hasAssociation('assoc'));
        $associationMetadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');
        $resourceMetadata->associations['assoc'] = $associationMetadata;
        $this->assertTrue($resourceMetadata->hasAssociation('assoc'));
        $this->assertSame($associationMetadata, $resourceMetadata->getAssociationMetadata('assoc'));
    }

    /**
     * @covers \ZfrRest\Resource\Metadata\ResourceMetadata
     */
    public function testAssertHasDefaultHydrator()
    {
        $resourceMetadata = new ResourceMetadata('stdClass');
        $this->assertSame('DoctrineModule\Stdlib\Hydrator\DoctrineObject', $resourceMetadata->getHydratorName());
    }

    /**
     * @covers \ZfrRest\Resource\Metadata\ResourceMetadata
     */
    public function testCanCreateEmptyResource()
    {
        $resourceMetadata = new ResourceMetadata('stdClass');
        $metadata                        = $this->getMock('Doctrine\\Common\\Persistence\\Mapping\\ClassMetadata');
        $resourceMetadata->classMetadata = $metadata;
        $reflectionClass                 = new ReflectionClass('stdClass');

        $metadata->expects($this->any())->method('getReflectionClass')->will($this->returnValue($reflectionClass));

        $resource = $resourceMetadata->createResource();

        $this->assertInstanceOf('ZfrRest\Resource\ResourceInterface', $resource);
        $this->assertSame($resource->getMetadata(), $resourceMetadata);
        $this->assertInstanceOf('stdClass', $resource->getData());
    }

    /**
     * @covers \ZfrRest\Resource\Metadata\ResourceMetadata
     */
    public function testCanCreateEmptyResourceWithParameter()
    {
        $resourceMetadata = new ResourceMetadata('ReflectionFunction');
        $metadata                        = $this->getMock('Doctrine\\Common\\Persistence\\Mapping\\ClassMetadata');
        $resourceMetadata->classMetadata = $metadata;

        $reflectionClass  = new \ReflectionClass('ReflectionFunction');
        $metadata->expects($this->any())->method('getReflectionClass')->will($this->returnValue($reflectionClass));

        $resource = $resourceMetadata->createResource('substr');

        $this->assertInstanceOf('ZfrRest\Resource\ResourceInterface', $resource);
        $this->assertSame($resource->getMetadata(), $resourceMetadata);
        $this->assertInstanceOf('ReflectionFunction', $resource->getData());
    }
}
