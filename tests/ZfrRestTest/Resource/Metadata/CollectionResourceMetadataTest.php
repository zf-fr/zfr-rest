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
use ZfrRest\Resource\Metadata\CollectionResourceMetadata;

/**
 * Tests for {@see \ZfrRest\Resource\Metadata\ResourceMetadata}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class CollectionResourceMetadataTest extends TestCase
{
    /**
     * @covers \ZfrRest\Resource\CollectionResourceMetadata
     */
    public function testResourceMetadata()
    {
        $resourceMetadata = new CollectionResourceMetadata('stdClass');

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
    }

    /**
     * @covers \ZfrRest\Resource\CollectionResourceMetadata
     */
    public function testAssertHasDefaultHydrator()
    {
        $resourceMetadata = new CollectionResourceMetadata('stdClass');
        $this->assertSame('DoctrineModule\Stdlib\Hydrator\DoctrineObject', $resourceMetadata->getHydratorName());
    }
}
