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
use ZfrRest\Resource\ResourceMetadata;

/**
 * Tests for {@see \ZfrRest\Resource\ResourceMetadata}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
class ResourceMetadataTest extends TestCase
{
    /**
     * @covers \ZfrRest\Resource\ResourceMetadata
     */
    public function testResourceMetadata()
    {
        $metadata         = $this->getMock('Doctrine\\Common\\Persistence\\Mapping\\ClassMetadata');
        $resourceMetadata = new ResourceMetadata($metadata);

        $this->assertSame($metadata, $resourceMetadata->getClassMetadata());

        $resourceMetadata->setControllerName('test');
        $this->assertSame('test', $resourceMetadata->getControllerName());
        $resourceMetadata->setControllerName(null);
        $this->assertSame(null, $resourceMetadata->getControllerName());

        $resourceMetadata->setInputFilterName('test');
        $this->assertSame('test', $resourceMetadata->getInputFilterName());
        $resourceMetadata->setInputFilterName(null);
        $this->assertSame(null, $resourceMetadata->getInputFilterName());

        $resourceMetadata->setHydratorName('test');
        $this->assertSame('test', $resourceMetadata->getHydratorName());
        $resourceMetadata->setHydratorName(null);
        $this->assertSame(null, $resourceMetadata->getHydratorName());

        $resourceMetadata->setEncoderNames(array('name' => 'encoder'));
        $this->assertSame(array('name' => 'encoder'), $resourceMetadata->getEncoderNames());

        $resourceMetadata->setDecoderNames(array('name' => 'decoder'));
        $this->assertSame(array('name' => 'decoder'), $resourceMetadata->getDecoderNames());

        $resourceMetadata->setAssociations(array('association'));
        $this->assertSame(array('association'), $resourceMetadata->getAssociations());
    }
}
