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
use ZfrRest\Resource\Resource;

/**
 * Tests for {@see \ZfrRest\Resource\Resource}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class ResourceTest extends TestCase
{
    /**
     * @covers \ZfrRest\Resource\Resource::__construct
     * @covers \ZfrRest\Resource\Resource::getResource
     * @covers \ZfrRest\Resource\Resource::getMetadata
     * @covers \ZfrRest\Resource\Resource::isCollection
     *
     * @dataProvider collectionResourceProvider
     */
    public function testResource($instance, $isCollection)
    {
        $metadata = $this->createMetadata();

        $metadata
            ->getClassMetadata()
            ->getReflectionClass()
            ->expects($this->any())
            ->method('isInstance')
            ->will($this->returnValue(! $isCollection));

        $resource = new Resource($instance, $metadata);

        $this->assertSame($instance, $resource->getData());
        $this->assertSame($metadata, $resource->getMetadata());
        $this->assertSame($isCollection, $resource->isCollection());
    }

    /**
     * @covers \ZfrRest\Resource\Resource::__construct
     * @covers \ZfrRest\Exception\InvalidResourceException::invalidResourceProvided
     */
    public function testDisallowsInvalidResource()
    {
        $metadata = $this->createMetadata();

        $metadata
            ->getClassMetadata()
            ->getReflectionClass()
            ->expects($this->any())
            ->method('isInstance')
            ->will($this->returnValue(false));

        $this->setExpectedException('ZfrRest\\Resource\\Exception\\InvalidResourceException');

        new Resource(new \stdClass(), $metadata);
    }

    /**
     * Data provider for various collection types
     *
     * @return array
     */
    public function collectionResourceProvider()
    {
        return array(
            array($this->getMock('Iterator'), true),
            array($this->getMock('Doctrine\\Common\\Collections\\Selectable'), true),
            array($this->getMock('Doctrine\\Common\\Collections\\Collection'), true),
            array(array(), true),
            array(new \stdClass(), false),
        );
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\ZfrRest\Resource\Metadata\ResourceMetadataInterface
     */
    private function createMetadata()
    {
        $resourceMetadata = $this->getMock('ZfrRest\\Resource\\Metadata\\ResourceMetadataInterface');
        $metadata         = $this->getMock('Doctrine\\Common\\Persistence\\Mapping\\ClassMetadata');
        $reflectionClass  = $this->getMock('ReflectionClass', array(), array(), '', false);

        $resourceMetadata->expects($this->any())->method('getClassMetadata')->will($this->returnValue($metadata));
        $metadata->expects($this->any())->method('getReflectionClass')->will($this->returnValue($reflectionClass));

        return $resourceMetadata;
    }
}
