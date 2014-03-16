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

namespace ZfrRestTest\View\Renderer;

use Zend\Stdlib\Hydrator\HydratorPluginManager;
use ZfrRest\Resource\Metadata\ResourceMetadataFactory;
use ZfrRest\Resource\Resource;
use ZfrRest\View\Model\ResourceModel;
use ZfrRest\View\Renderer\DefaultResourceRenderer;
use ZfrRestTest\Asset\Resource\Metadata\Annotation\Address;
use ZfrRestTest\Asset\Resource\Metadata\Annotation\User;
use ZfrRestTest\Util\ServiceManagerFactory;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 *
 * @group Coverage
 * @covers \ZfrRest\View\Renderer\DefaultResourceRenderer
 */
class DefaultResourceRendererTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ResourceMetadataFactory
     */
    protected $resourceMetadataFactory;

    /**
     * @var HydratorPluginManager
     */
    protected $hydratorManager;

    /**
     * @var DefaultResourceRenderer
     */
    protected $resourceRenderer;

    public function setUp()
    {
        $serviceManager = ServiceManagerFactory::getServiceManager();

        $this->resourceMetadataFactory = $serviceManager->get('ZfrRest\Resource\Metadata\ResourceMetadataFactory');
        $this->hydratorManager         = $serviceManager->get('HydratorManager');

        $this->resourceRenderer  = new DefaultResourceRenderer($this->resourceMetadataFactory, $this->hydratorManager);
    }

    public function testCanRenderSingleResourceWithoutAssociation()
    {
        $address = new Address();

        $user = new User();
        $user->setId(2);
        $user->setUsername('bakura');
        $user->setAddress($address);

        $metadata = $this->resourceMetadataFactory->getMetadataForClass(
            'ZfrRestTest\Asset\Resource\Metadata\Annotation\User'
        );

        // In this test, we enforce that association extraction is set to NONE
        $metadata->propertyMetadata['associations']['address']['extraction'] = 'NONE';

        $resourceModel = new ResourceModel(new Resource($user, $metadata));
        $payload       = $this->resourceRenderer->render($resourceModel);

        $expectedPayload = [
            'id'       => 2,
            'username' => 'bakura'
        ];

        $this->assertEquals($expectedPayload, $payload);
    }

    public function testCanRenderSingleResourceWithAssociationAsId()
    {
        $address = new Address();
        $address->setId(43);

        $user = new User();
        $user->setId(2);
        $user->setUsername('bakura');
        $user->setAddress($address);

        $metadata = $this->resourceMetadataFactory->getMetadataForClass(
            'ZfrRestTest\Asset\Resource\Metadata\Annotation\User'
        );

        // In this test, we enforce that association extraction is set to ID
        $metadata->propertyMetadata['associations']['address']['extraction'] = 'ID';

        $resourceModel = new ResourceModel(new Resource($user, $metadata));
        $payload       = $this->resourceRenderer->render($resourceModel);

        $expectedPayload = [
            'id'       => 2,
            'username' => 'bakura',
            'address'  => 43
        ];

        $this->assertEquals($expectedPayload, $payload);
    }

    public function testCanRenderSingleResourceWithAssociationAsEmbed()
    {
        $address = new Address();
        $address->setId(43);
        $address->setCountry('France');

        $user = new User();
        $user->setId(2);
        $user->setUsername('bakura');
        $user->setAddress($address);

        $metadata = $this->resourceMetadataFactory->getMetadataForClass(
            'ZfrRestTest\Asset\Resource\Metadata\Annotation\User'
        );

        // In this test, we enforce that association extraction is set to EMBED
        $metadata->propertyMetadata['associations']['address']['extraction'] = 'EMBED';

        $resourceModel = new ResourceModel(new Resource($user, $metadata));
        $payload       = $this->resourceRenderer->render($resourceModel);

        $expectedPayload = [
            'id'       => 2,
            'username' => 'bakura',
            'address'  => 43
        ];

        $this->assertEquals($expectedPayload, $payload);
    }
}
