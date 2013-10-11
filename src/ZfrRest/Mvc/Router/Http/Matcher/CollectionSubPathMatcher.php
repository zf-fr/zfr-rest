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

namespace ZfrRest\Mvc\Router\Http\Matcher;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Traversable;
use Zend\EventManager\EventManager;
use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\Http\Request as HttpRequest;
use ZfrRest\Mvc\Exception\RuntimeException;
use ZfrRest\Resource\Resource;
use ZfrRest\Resource\ResourceInterface;

/**
 * Matcher for a collection sub-path
 *
 * This matcher is executed when matching a collection. For instance, with the URI "/users/5", this
 * matcher will be executed for the "/5" sub path, the resource passed to the "matchSubPath" method
 * being the users collection
 *
 * This matcher can also be executed at the end of a path (for instance "/users"). In this case, it
 * will trigger an event to allow filtering the collection
 *
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class CollectionSubPathMatcher implements SubPathMatcherInterface, EventManagerAwareInterface
{
    /**
     * @var EventManagerInterface
     */
    protected $eventManager;

    /**
     * {@inheritDoc}
     */
    public function matchSubPath(
        ResourceInterface $resource,
        $subPath,
        HttpRequest $request,
        SubPathMatch $previousMatch = null
    ) {
        if (!$resource->isCollection()) {
            return null;
        }

        $path = trim($subPath, '/');

        if (empty($path)) {
            return new SubPathMatch($this->filterAssociation($resource, $request), $subPath);
        }

        $pathChunks    = explode('/', $path);
        $identifier    = array_shift($pathChunks);
        $classMetadata = $resource->getMetadata()->getClassMetadata();
        $data          = $this->findItem($resource->getData(), $classMetadata->getIdentifierFieldNames(), $identifier);

        if (null === $data) {
            return null;
        }

        return new SubPathMatch(
            new Resource($data, $resource->getMetadata()),
            $identifier,
            $previousMatch
        );
    }

    /**
     * {@inheritDoc}
     */
    public function setEventManager(EventManagerInterface $eventManager)
    {
        $eventManager->setIdentifiers(array(
            __CLASS__,
            get_called_class()
        ));

        $this->eventManager = $eventManager;
    }

    /**
     * {@inheritDoc}
     */
    public function getEventManager()
    {
        if (null === $this->eventManager) {
            $this->setEventManager(new EventManager());
        }

        return $this->eventManager;
    }

    /**
     * Retrieves a single item in the collection by its identifier
     *
     * @param  mixed $data
     * @param  array $identifierNames
     * @param  mixed $identifier
     * @return mixed|null
     *
     * @throws RuntimeException on composite identifiers (not yet supported)
     */
    protected function findItem($data, array $identifierNames, $identifier)
    {
        if (count($identifierNames) > 1) {
            throw new RuntimeException(get_class($this) . ' is not able to handle composite identifiers');
        }

        if (!$data instanceof Selectable && $data instanceof Traversable) {
            $data = new ArrayCollection(iterator_to_array($data));
        }

        $criteria = new Criteria();
        $criteria->andWhere($criteria->expr()->eq(reset($identifierNames), $identifier));

        $found = $data->matching($criteria);

        return $found->isEmpty() ? null : $found->first();
    }

    /**
     * Filters the given resource by using the request object, then return the filtered subset
     *
     * @param  ResourceInterface $resource
     * @param  HttpRequest       $request
     * @return ResourceInterface
     */
    protected function filterAssociation(ResourceInterface $resource, HttpRequest $request)
    {
        // Trigger an event to allow custom filtering
        $this->eventManager->trigger(
            CollectionFilteringEvent::EVENT_COLLECTION_FILTERING,
            new CollectionFilteringEvent($resource, $request)
        );

        return $resource;
    }
}
