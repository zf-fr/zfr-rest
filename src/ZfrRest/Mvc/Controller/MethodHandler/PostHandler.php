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

namespace ZfrRest\Mvc\Controller\MethodHandler;

use Zend\InputFilter\InputFilterPluginManager;
use Zend\Stdlib\Hydrator\HydratorPluginManager;
use ZfrRest\Http\Exception\Client\MethodNotAllowedException;
use ZfrRest\Mvc\Controller\AbstractRestfulController;
use ZfrRest\Resource\ResourceInterface;
use ZfrRest\View\Model\ResourceModel;

/**
 * Handler for the POST method verb
 *
 * The POST request allow the client to create a new resource
 *
 * @link    http://www.w3.org/Protocols/rfc2616/rfc2616-sec9.html#sec9.5
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class PostHandler implements MethodHandlerInterface
{
    /**
     * Traits
     */
    use DataValidationTrait;
    use DataHydrationTrait;

    /**
     * Constructor
     *
     * @param InputFilterPluginManager $inputFilterPluginManager
     * @param HydratorPluginManager    $hydratorPluginManager
     */
    public function __construct(
        InputFilterPluginManager $inputFilterPluginManager,
        HydratorPluginManager $hydratorPluginManager
    ) {
        $this->inputFilterPluginManager = $inputFilterPluginManager;
        $this->hydratorPluginManager    = $hydratorPluginManager;
    }

    /**
     * Handler for POST method
     *
     * POST method is used to create a new resource. On successful creation, POST method should return a HTTP status
     * 201, with a Location header containing the URL of the newly created resource. We are doing several things for the
     * user automatically:
     *      - we validate post data with the input filter defined in metadata
     *      - we hydrate valid data
     *      - we pass the object to the post method of the controller
     *
     * As you can see, the post method have three arguments: the object that is inserted, the resource metadata and
     * the resource itself (which is the Collection where the object is added)
     *
     * {@inheritDoc}
     * @throws MethodNotAllowedException
     */
    public function handleMethod(AbstractRestfulController $controller, ResourceInterface $resource)
    {
        // If no post method is defined on the controller, then we cannot do anything
        if (!method_exists($controller, 'post')) {
            throw new MethodNotAllowedException();
        }

        $singleResource = $resource->getMetadata()->createResource();
        $data           = json_decode($controller->getRequest()->getContent(), true);

        if ($controller->getAutoValidate()) {
            $data = $this->validateData($singleResource, $data, 'post');
        }

        if ($controller->getAutoHydrate()) {
            $data = $this->hydrateData($singleResource, $data);
        }

        $result = $controller->post($data);

        // Set the Location header with the URL of the newly created resource
        if ($result instanceof ResourceModel) {
            $location = $controller->url()->fromRoute(null, ['resource' => $result->getResource()]);

            $controller->getResponse()->setStatusCode(201)
                                      ->getHeaders()->addHeaderLine('Location', $location);
        }

        return $result;
    }
}
