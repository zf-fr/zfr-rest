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
use ReflectionClass;
use ZfrRest\Resource\Metadata\ResourceMetadata;

/**
 * Tests for {@see \ZfrRest\Resource\Metadata\ResourceMetadata}
 *
 * @author Michaël Gallego <mic.gallego@gmail.com>
 * @covers \ZfrRest\Resource\Metadata\ResourceMetadata
 */
class ResourceMetadataTest extends TestCase
{
    public function testCanCheckAssociations()
    {
        $resourceMetadata = new ResourceMetadata('stdClass');
        $resourceMetadata->associations['tweets'] = new \stdClass();

        $this->assertTrue($resourceMetadata->hasAssociation('tweets'));
        $this->assertFalse($resourceMetadata->hasAssociation('retweets'));
    }

    public function testCanCreateSimpleResource()
    {
        $classMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $classMetadata->expects($this->once())
                      ->method('getReflectionClass')
                      ->will($this->returnValue(new ReflectionClass('ZfrRestTest\Resource\Asset\ResourceAsset')));

        $resourceMetadata = new ResourceMetadata('ZfrRestTest\Resource\Asset\ResourceAsset');
        $resourceMetadata->classMetadata = $classMetadata;

        $resource = $resourceMetadata->createResource();
        $this->assertInstanceOf('ZfrRestTest\Resource\Asset\ResourceAsset', $resource->getData());
        $this->assertSame($resourceMetadata, $resource->getMetadata());
    }

    public function testCanCreateComplexResource()
    {
        $classMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');
        $classMetadata->expects($this->once())
                      ->method('getReflectionClass')
                      ->will($this->returnValue(
                          new ReflectionClass(
                              'ZfrRestTest\Resource\Asset\ResourceWithParametersAsset'
                          )
                      ));

        $resourceMetadata = new ResourceMetadata('ZfrRestTest\Resource\Asset\ResourceWithParametersAsset');
        $resourceMetadata->classMetadata = $classMetadata;

        $resource = $resourceMetadata->createResource('foo');
        $this->assertInstanceOf('ZfrRestTest\Resource\Asset\ResourceWithParametersAsset', $resource->getData());
        $this->assertEquals('foo', $resource->getData()->getParam());
        $this->assertSame($resourceMetadata, $resource->getMetadata());
    }
}
