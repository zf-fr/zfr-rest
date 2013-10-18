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

use Zend\EventManager\Event;
use Zend\Http\Request as HttpRequest;
use ZfrRest\Resource\ResourceInterface;

/**
 * This event allows custom filtering logic for collection (for instance using HTTP query params)
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class CollectionFilteringEvent extends Event
{
    /**
     * Key for event
     */
    const EVENT_COLLECTION_FILTERING = 'collectionFiltering';

    /**
     * @var ResourceInterface
     */
    protected $resource;

    /**
     * @var HttpRequest
     */
    protected $request;

    /**
     * Constructor
     *
     * @param ResourceInterface $resource
     * @param HttpRequest       $request
     */
    public function __construct(ResourceInterface $resource, HttpRequest $request)
    {
        $this->resource = $resource;
        $this->request  = $request;
    }

    /**
     * Get the resource
     *
     * @return ResourceInterface
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * Get the HTTP request
     *
     * @return HttpRequest
     */
    public function getRequest()
    {
        return $this->request;
    }
}
