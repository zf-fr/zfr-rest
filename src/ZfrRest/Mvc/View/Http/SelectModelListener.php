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

namespace ZfrRest\Mvc\View\Http;

use Zend\EventManager\EventManagerInterface;
use Zend\EventManager\ListenerAggregateInterface;
use Zend\Http\Request as HttpRequest;
use Zend\Mvc\MvcEvent;
use Zend\Stdlib\ResponseInterface;
use Zend\View\Model\ModelInterface;
use ZfrRest\Mvc\Exception;
use ZfrRest\View\Model\ModelPluginManager;

/**
 * SelectModelListener. This listener is used to select the appropriate ModelInterface instance
 * according to the Accept header
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class SelectModelListener implements ListenerAggregateInterface
{
    /**
     * @var \Zend\Stdlib\CallbackHandler[]
     */
    protected $listeners = array();

    /**
     * @var ModelPluginManager
     */
    protected $modelPluginManager;


    /**
     * Constructor
     *
     * @param ModelPluginManager $modelPluginManager
     */
    public function __construct(ModelPluginManager $modelPluginManager)
    {
        $this->modelPluginManager = $modelPluginManager;
    }

    /**
     * {@inheritDoc}
     */
    public function attach(EventManagerInterface $events)
    {
        $sharedManager = $events->getSharedManager();

        $events->attach(MvcEvent::EVENT_DISPATCH_ERROR, array($this, 'injectErrorModel'), 80);
        $sharedManager->attach('Zend\Stdlib\DispatchableInterface', MvcEvent::EVENT_DISPATCH, array($this, 'selectModel'), -60);
    }

    /**
     * {@inheritDoc}
     */
    public function detach(EventManagerInterface $events)
    {
        foreach ($this->listeners as $index => $listener) {
            if ($events->detach($listener)) {
                unset($this->listeners[$index]);
            }
        }
    }

    /**
     * Select the correct ModelInterface instance by matching the values of the Accept header to a ModelInterface
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function selectModel(MvcEvent $e)
    {
        $result = $e->getResult();
        if ($result instanceof ModelInterface || $result instanceof ResponseInterface) {
            return;
        }

        $request = $e->getRequest();
        if (!$request instanceof HttpRequest) {
            return;
        }

        $contentType = $this->getContentType($request);
        $model       = $this->modelPluginManager->create($contentType);

        if ($result !== null) {
            $model->setVariables($result);
        }

        $e->setResult($model);
    }

    /**
     * When an exception is thrown, this listener proxies to selectModel. If, according to the Accept header,
     * we get a Zend\View\Model\ViewModel instance, this means we are in "website" context, so we just return
     * to let the other listeners render the template error.
     *
     * Otherwise (if we have a JsonModel or FeedModel or anything else...) we just set the view model and stop
     * propagation so that the response only contains the error message and status code
     *
     * @param  MvcEvent $e
     * @return void
     */
    public function injectErrorModel(MvcEvent $e)
    {
        $this->selectModel($e);

        $result = $e->getResult();
        if (!$result instanceof ModelInterface) {
            return;
        }

        // If it's an exact instance of Zend\View\Model\ViewModel we return as we want to let the other
        // listeners to inject layout
        if (get_class($result) === 'Zend\View\Model\ViewModel') {
            return;
        }

        // Otherwise, we stop propagation and set the view model
        $e->setViewModel($result);
        $e->stopPropagation();
    }

    /**
     * Get the content type with higher priority in the request
     *
     * @param  HttpRequest $request
     * @return string|null
     */
    protected function getContentType(HttpRequest $request)
    {
        /** @var $acceptHeader \Zend\Http\Header\Accept */
        $acceptHeader = $request->getHeader('Accept', null);
        if ($acceptHeader === null) {
            return null;
        }

        $acceptValues = $acceptHeader->getPrioritized();
        $acceptValue  = reset($acceptValues);

        return $acceptValue->getTypeString();
    }
}
