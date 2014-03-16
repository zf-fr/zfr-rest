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

namespace ZfrRest\View\Renderer;

use Zend\Paginator\Paginator;
use Zend\Stdlib\Hydrator\HydratorPluginManager;
use ZfrRest\Resource\ResourceInterface;
use ZfrRest\View\Model\ResourceModel;

/**
 * This is a very simple renderer that only outputs the resource as JSON, either directly in the payload for a single
 * resource, or wrapping it around a "data" top-level attributes for multiple resources
 *
 * This renderer does not assume to render any links, it's voluntarily simple. Here is an example of the generated
 * payload when asking a simple resource like GET /posts/1:
 *
 * {
 *     "id": 1,
 *     "title": "ZfrRest is awesome",
 *     "author": {
 *         "id": 50,
 *         "name": "Michaël Gallego"
 *     }
 * }
 *
 * Or when using a collection:
 *
 * {
 *     "limit": 10,
 *     "offset": 50,
 *     "total": 600,
 *     "data": [
 *         {
 *             "id": 1,
 *             "title": "PHP will domine the world!",
 *             "author": {
 *                 "id": 56,
 *                 "name": "Marco Pivetta"
 *             }
 *         },
 *         {
 *             "id": 2,
 *             "title": "PHP generators are awesome",
 *             "author": {
 *                 "id": 95,
 *                 "name": "Daniel Gimenes"
 *             }
 *         }
 *     ]
 * }
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class SimpleResourceRenderer extends AbstractResourceRenderer
{
    /**
     * @var HydratorPluginManager
     */
    protected $hydratorPluginManager;

    /**
     * Constructor
     *
     * @param HydratorPluginManager $hydratorPluginManager
     */
    public function __construct(HydratorPluginManager $hydratorPluginManager)
    {
        $this->hydratorPluginManager = $hydratorPluginManager;
    }

    /**
     * {@inheritDoc}
     */
    public function render($nameOrModel, $values = null)
    {
        if (!$nameOrModel instanceof ResourceModel) {
            return;
        }

        $resource = $nameOrModel->getResource();

        if ($resource->isCollection()) {
            $payload = $this->renderCollection($resource);
        } else {
            $payload = $this->renderItem($resource);
        }

        return json_encode($payload);
    }

    /**
     * {@inheritDoc}
     */
    public function renderItem(ResourceInterface $resource)
    {
        $hydratorName = $resource->getMetadata()->getHydratorName();
        $hydrator     = $this->hydratorPluginManager->get($hydratorName);

        return $hydrator->extract($resource->getData());
    }

    /**
     * {@inheritDoc}
     */
    public function renderCollection(ResourceInterface $resource)
    {
        $hydratorName = $resource->getMetadata()->getHydratorName();
        $hydrator     = $this->hydratorPluginManager->get($hydratorName);

        $data    = $resource->getData();
        $payload = [];

        if ($data instanceof Paginator) {
            $payload = [
                'limit'  => $data->getItemCountPerPage(),
                'offset' => ($data->getCurrentPageNumber() - 1) * $data->getItemCountPerPage(),
                'total'  => $data->getTotalItemCount()
            ];
        }

        foreach ($data as $item) {
            $payload['data'][] = $hydrator->extract($item);
        }

        return $payload;
    }
}
