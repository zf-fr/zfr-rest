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

use Zend\Stdlib\Hydrator\Aggregate\AggregateHydrator;
use Zend\Stdlib\Hydrator\Aggregate\ExtractEvent;
use Zend\Stdlib\Hydrator\HydratorPluginManager;
use ZfrRest\Stdlib\Hydrator\Normalizer\OutputNormalizerInterface;
use ZfrRest\Stdlib\Hydrator\Normalizer\SimpleNormalizer;

/**
 * The RestAggregateHydrator is an aggregate hydrator that is mainly used for extraction purpose, when outputting
 * data.
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class RestAggregateHydrator extends AggregateHydrator
{
    /**
     * Hydrators are runned in a specific order to allow pre-process at each step
     */
    const PAGINATOR_PRIORITY = 500;

    /**
     * @var HydratorPluginManager
     */
    protected $hydratorManager;

    /**
     * @var OutputNormalizerInterface
     */
    protected $outputNormalizer;

    /**
     * @param HydratorPluginManager     $hydratorManager
     * @param OutputNormalizerInterface $normalizer
     */
    public function __construct(HydratorPluginManager $hydratorManager, OutputNormalizerInterface $normalizer = null)
    {
        $this->hydratorManager = $hydratorManager;
        $this->outputNormalizer = new SimpleNormalizer();
        $eventManager = $this->getEventManager();
        $eventManager->attach(ExtractEvent::EVENT_EXTRACT, array($this, 'extractPaginatorData'), self::PAGINATOR_PRIORITY);
    }

    /**
     * Extract data from a Paginator instance, and normalize it using the normalizer attached
     *
     * @param ExtractEvent $event
     */
    public function extractPaginatorData(ExtractEvent $event)
    {
        $paginatorHydrator = $this->hydratorManager->get('ZfrRest\Stdlib\Hydrator\PaginatorHydrator');
        $data              = $paginatorHydrator->extract($event->getExtractionObject());
        $normalizedData    = $this->outputNormalizer->normalizePaginatorData($data, $event->getExtractionObject());

        $event->mergeExtractedData($normalizedData);
    }
}
