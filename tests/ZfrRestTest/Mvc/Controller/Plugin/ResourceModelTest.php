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

namespace ZfrRestTest\Mvc\Controller\Plugin;

use PHPUnit_Framework_TestCase;
use ZfrRest\Mvc\Controller\AbstractRestfulController;
use ZfrRest\Mvc\Controller\Plugin\ResourceModel;

/**
 * @licence MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 *
 * @group Coverage
 * @covers \ZfrRest\Mvc\Controller\Plugin\ResourceModel
 */
class ResourceModelTest extends PHPUnit_Framework_TestCase
{
    public function testThrowExceptionIfNotAbstractRestfulController()
    {
        $this->setExpectedException('ZfrRest\Mvc\Exception\RuntimeException');

        $plugin = new ResourceModel();
        $plugin(new \stdClass());
    }

    public function testCanCreateResourceModelWithMetadata()
    {
        $controller = new AbstractRestfulController();
        $plugin     = new ResourceModel();
        $plugin->setController($controller);

        $reflClass = $this->getMock('ReflectionClass', [], [], '', false);
        $reflClass->expects($this->once())->method('isInstance')->will($this->returnValue(true));

        $metadata = $this->getMock('ZfrRest\Resource\Metadata\ResourceMetadataInterface');
        $metadata->expects($this->once())->method('getReflectionClass')->will($this->returnValue($reflClass));

        $data          = new \stdClass();
        $resourceModel = $plugin($data, $metadata);

        $this->assertInstanceOf('ZfrRest\View\Model\ResourceModel', $resourceModel);
        $this->assertSame($data, $resourceModel->getResource()->getData());
        $this->assertSame($metadata, $resourceModel->getResource()->getMetadata());
    }
}
