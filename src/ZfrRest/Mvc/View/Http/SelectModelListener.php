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
use ZfrRest\Mime\FormatDecoder;
use ZfrRest\Mvc\Exception;
use ZfrRest\View\Model\ModelPluginManager;

/**
 * SelectModelListener. This listener is used to select the appropriate ModelInterface instance
 * according to the Accept header
 *
 * @license MIT
 * @since   0.0.1
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
     * @var FormatDecoder
     */
    protected $formatDecoder;


    /**
     * Constructor
     *
     * @param FormatDecoder $formatDecoder
     */
    public function __construct(FormatDecoder $formatDecoder)
    {
        $this->formatDecoder = $formatDecoder;
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
     * Set the model plugin manager
     *
     * @param  ModelPluginManager $pluginManager
     * @return SelectModelListener
     */
    public function setModelPluginManager(ModelPluginManager $pluginManager)
    {
        $this->modelPluginManager = $pluginManager;
        return $this;
    }

    /**
     * Get the model plugin manager
     *
     * @return ModelPluginManager
     */
    public function getModelPluginManager()
    {
        if ($this->modelPluginManager === null) {
            $this->modelPluginManager = new ModelPluginManager();
        }

        return $this->modelPluginManager;
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

        $format = $this->getRequestFormat($request);
        $model  = $this->getModelPluginManager()->get($format);

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

        /** @var $request HttpRequest */
        $request = $e->getRequest();
        $format  = $this->getRequestFormat($request);

        // If want to render HTML, just let the other listeners insert the template
        if ($format === 'html') {
            return;
        }

        // Otherwise, we stop propagation and set the view model
        $e->setViewModel($result);
        $e->stopPropagation();
    }

    /**
     * Get the format of the request (html, json...) from the Accept header
     *
     * @param  HttpRequest $request
     * @return string|null
     */
    protected function getRequestFormat(HttpRequest $request)
    {
        /** @var $acceptHeader \Zend\Http\Header\Accept */
        $acceptHeader = $request->getHeader('Accept', null);
        if ($acceptHeader === null) {
            return null;
        }

        $acceptValues = $acceptHeader->getPrioritized();

        foreach ($acceptValues as $mimeType) {
            $format = $this->formatDecoder->decode($mimeType);
            if ($format !== null) {
                return $format;
            }
        }

        return null;
    }
}
