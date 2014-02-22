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

use ZfrRest\Resource\Metadata\ResourceMetadataFactory;

/**
 * @licence MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group Coverage
 * @covers \ZfrRest\Resource\Metadata\ResourceMetadataFactory
 */
class ResourceMetadataFactoryTest extends \PHPUnit_Framework_TestCase
{
    public function testAssertDoctrineClassMetadataIsFilled()
    {
        $classMetadataFactory = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadataFactory');
        $driver               = $this->getMock('Metadata\Driver\DriverInterface');

        $metadataFactory = new ResourceMetadataFactory($classMetadataFactory, $driver);

        $classMetadata = $this->getMock('Doctrine\Common\Persistence\Mapping\ClassMetadata');

        $classMetadataFactory->expects($this->once())
                             ->method('getMetadataFor')
                             ->will($this->returnValue($classMetadata));

        $resourceMetadata = $metadataFactory->getMetadataForClass('stdClass');

        $this->assertSame($classMetadata, $resourceMetadata->propertyMetadata['classMetadata']);
    }
}
