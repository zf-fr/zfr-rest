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

namespace ZfrRest\Mvc\Controller\Event;

use Zend\EventManager\Event;
use Zend\Stdlib\Hydrator\HydratorInterface;
use Zend\Stdlib\Hydrator\HydratorPluginManager;
use ZfrRest\Mvc\Exception\RuntimeException;
use ZfrRest\Resource\ResourceInterface;

/**
 * @author  Daniel Gimenes <daniel@danielgimenes.com.br>
 * @licence MIT
 */
class HydrationEvent extends Event
{
    /**
     * Event names
     */
    const EVENT_HYDRATE_PRE  = 'hydrate.pre';
    const EVENT_HYDRATE_POST = 'hydrate.post';

    /**
     * @var bool
     */
    protected $autoHydrate = true;

    /**
     * @var ResourceInterface
     */
    protected $resource;

    /**
     * @var HydratorPluginManager
     */
    protected $hydratorManager;

    /**
     * @var null|HydratorInterface
     */
    protected $hydrator;

    /**
     * @param ResourceInterface     $resource
     * @param HydratorPluginManager $hydratorManager
     */
    public function __construct(ResourceInterface $resource, HydratorPluginManager $hydratorManager)
    {
        $this->resource        = $resource;
        $this->hydratorManager = $hydratorManager;
    }

    /**
     * @param bool $autoHydrate
     */
    public function setAutoHydrate($autoHydrate)
    {
        $this->autoHydrate = (bool) $autoHydrate;
    }

    /**
     * @return bool
     */
    public function getAutoHydrate()
    {
        return $this->autoHydrate;
    }

    /**
     * @return ResourceInterface
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return HydratorPluginManager
     */
    public function getHydratorManager()
    {
        return $this->hydratorManager;
    }

    /**
     * @param HydratorInterface $hydrator
     */
    public function setHydrator(HydratorInterface $hydrator)
    {
        $this->hydrator = $hydrator;
    }

    /**
     * Lazy load the hydrator from plugin manager (or retrieve the one that is set)
     *
     * @return null|HydratorInterface
     * @throws RuntimeException
     */
    public function getHydrator()
    {
        if (!$this->hydrator instanceof HydratorInterface) {
            if (!($hydratorName = $this->resource->getMetadata()->getHydratorName())) {
                throw new RuntimeException('No hydrator name has been found in resource metadata');
            }

            $this->hydrator = $this->hydratorManager->get($hydratorName);
        }

        return $this->hydrator;
    }
}
