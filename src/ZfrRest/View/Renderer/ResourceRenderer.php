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

use Traversable;
use Zend\Paginator\Paginator;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Stdlib\Hydrator\HydratorPluginManager;
use Zend\View\Renderer\RendererInterface;
use Zend\View\Resolver\ResolverInterface;
use ZfrRest\View\Model\ResourceModel;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class ResourceRenderer implements RendererInterface
{
    /**
     * @var ResolverInterface
     */
    protected $resolver;

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
    public function getEngine()
    {
        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function setResolver(ResolverInterface $resolver)
    {
        $this->resolver = $resolver;
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
            $collectionMetadata = $resource->getMetadata()->getCollectionMetadata();
            $hydrator           = $this->hydratorPluginManager->get($collectionMetadata->getHydratorName());

            $payload = $this->renderCollection($resource->getData(), $hydrator);
        } else {
            $resourceMetadata = $resource->getMetadata();
            $hydrator         = $this->hydratorPluginManager->get($resourceMetadata->getHydratorName());

            $payload = $this->renderItem($resource->getData(), $hydrator);
        }

        return json_encode($payload);
    }

    /**
     * Return the payload for a single item
     *
     * @param  mixed             $item
     * @param  HydratorInterface $hydrator
     * @return array
     */
    protected function renderItem($item, HydratorInterface $hydrator)
    {
        return $hydrator->extract($item);
    }

    /**
     * Return the payload for a collection
     *
     * By default, it creates some data if a paginator is found, and wrap all items under the "items" key
     *
     * @param  array|Traversable $collection
     * @param  HydratorInterface $hydrator
     * @return array
     */
    protected function renderCollection($collection, HydratorInterface $hydrator)
    {
        $payload = [];

        if ($collection instanceof Paginator) {
            $payload = [
                'limit'  => $collection->getItemCountPerPage(),
                'offset' => ($collection->getCurrentPageNumber() - 1) * $collection->getItemCountPerPage(),
                'total'  => $collection->getTotalItemCount()
            ];
        }

        foreach ($collection as $item) {
            $payload['items'][] = $this->renderItem($item, $hydrator);
        }

        return $payload;
    }
}
