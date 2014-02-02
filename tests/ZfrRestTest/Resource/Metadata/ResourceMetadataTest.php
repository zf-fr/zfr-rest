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

use PHPUnit_Framework_TestCase;
use ReflectionClass;
use ZfrRest\Resource\Metadata\CollectionResourceMetadata;
use ZfrRest\Resource\Metadata\ResourceMetadata;

/**
 * @licence MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group Coverage
 * @covers \ZfrRest\Resource\Metadata\ResourceMetadata
 */
class ResourceMetadataTest extends PHPUnit_Framework_TestCase
{
    public function testCanCreateSimpleResource()
    {
        $resourceMetadata = new ResourceMetadata('ZfrRestTest\Asset\Resource\SimpleResource');
        $resourceMetadata->reflection = new ReflectionClass('ZfrRestTest\Asset\Resource\SimpleResource');

        $resource = $resourceMetadata->createResource();
        $this->assertInstanceOf('ZfrRestTest\Asset\Resource\SimpleResource', $resource->getData());
        $this->assertSame($resourceMetadata, $resource->getMetadata());
    }

    public function testCanCreateComplexResource()
    {
        $resourceMetadata = new ResourceMetadata('ZfrRestTest\Asset\Resource\SimpleResourceWithParameters');
        $resourceMetadata->reflection = new ReflectionClass('ZfrRestTest\Asset\Resource\SimpleResourceWithParameters');

        $resource = $resourceMetadata->createResource('foo');
        $this->assertInstanceOf('ZfrRestTest\Asset\Resource\SimpleResourceWithParameters', $resource->getData());
        $this->assertEquals('foo', $resource->getData()->getParam());
        $this->assertSame($resourceMetadata, $resource->getMetadata());
    }

    public function testSettersAndGetters()
    {
        $resourceMetadata = new ResourceMetadata('stdClass');

        $data = [
            'classMetadata'      => $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata'),
            'controller'         => 'Controller',
            'inputFilter'        => 'InputFilter',
            'hydrator'           => 'Hydrator',
            'collectionMetadata' => $this->getMock('ZfrRest\Resource\Metadata\CollectionResourceMetadataInterface'),
            'associations'       => ['foo' => ['path' => 'foo']]
        ];

        foreach ($data as $key => $value) {
            $resourceMetadata->propertyMetadata[$key] = $value;
        }

        $this->assertSame($data['classMetadata'], $resourceMetadata->getClassMetadata());
        $this->assertEquals($data['controller'], $resourceMetadata->getControllerName());
        $this->assertEquals($data['inputFilter'], $resourceMetadata->getInputFilterName());
        $this->assertEquals($data['hydrator'], $resourceMetadata->getHydratorName());
        $this->assertSame($data['collectionMetadata'], $resourceMetadata->getCollectionMetadata());
        $this->assertTrue($resourceMetadata->hasAssociationMetadata('foo'));
        $this->assertInternalType('array', $resourceMetadata->getAssociationMetadata('foo'));
        $this->assertFalse($resourceMetadata->hasAssociationMetadata('bar'));
        $this->assertNull($resourceMetadata->getAssociationMetadata('bar'));
    }
}
