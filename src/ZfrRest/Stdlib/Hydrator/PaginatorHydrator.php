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

namespace ZfrRest\Stdlib\Hydrator;

use Zend\Paginator\Paginator;
use Zend\Stdlib\Hydrator\AbstractHydrator;
use Zend\Stdlib\Hydrator\HydratorPluginManager;
use ZfrRest\Paginator\ResourcePaginator;

/**
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class PaginatorHydrator extends AbstractHydrator
{
    /**
     * @var HydratorPluginManager
     */
    protected $hydratorManager;

    /**
     * @param HydratorPluginManager $hydratorManager
     */
    public function __construct(HydratorPluginManager $hydratorManager)
    {
        $this->hydratorManager = $hydratorManager;
    }

    /**
     * {@inheritDoc}
     */
    public function extract($object)
    {
        if (!$object instanceof ResourcePaginator) {
            return array();
        }

        $payload = array(
            'current_page'   => $object->getCurrentPageNumber(),
            'count_per_page' => $object->getItemCountPerPage()
        );

        $resourceHydrator = $object->getResourceMetadata()->getHydratorName();
        $resourceHydrator = $this->hydratorManager->get($resourceHydrator);

        foreach ($object as $item) {
            $payload['items'][] = $resourceHydrator->extract($item);
        }

        return $payload;
    }

    /**
     * {@inheritDoc}
     */
    public function hydrate(array $data, $object)
    {
        // TODO: Implement hydrate() method.
    }
}
