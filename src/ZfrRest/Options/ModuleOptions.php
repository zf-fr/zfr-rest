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

namespace ZfrRest\Options;

use Zend\Stdlib\AbstractOptions;

/**
 * ModuleOptions
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class ModuleOptions extends AbstractOptions
{
    /**
     * Key of the object manager fetched from the service locator
     *
     * @var string
     */
    protected $objectManager;

    /**
     * Listeners options (allow to activate/deactive listeners)
     *
     * @var ListenersOptions
     */
    protected $listenersOptions;

    /**
     * Controller behaviours options
     *
     * @var ControllerBehavioursOptions
     */
    protected $controllerBehavioursOptions;

    /**
     * Options for resource metadata
     *
     * @var ResourceMetadataOptions
     */
    protected $resourceMetadataOptions;

    /**
     * Plugin manager configuration for the content decoders
     *
     * @var array
     */
    protected $decoders = array();

    /**
     * Plugin manager configuration for the view models
     *
     * @var array
     */
    protected $models = array();

    /**
     * @param string $objectManager
     *
     * @return void
     */
    public function setObjectManager($objectManager)
    {
        $this->objectManager = (string) $objectManager;
    }

    /**
     * @return string
     */
    public function getObjectManager()
    {
        return $this->objectManager;
    }

    /**
     * @param  array $options
     *
     * @return void
     */
    public function setListeners(array $options)
    {
        $this->listenersOptions = new ListenersOptions($options);
    }

    /**
     * @return ListenersOptions
     */
    public function getListeners()
    {
        return $this->listenersOptions;
    }

    /**
     * @param  array $options
     *
     * @return void
     */
    public function setControllerBehaviours(array $options)
    {
        $this->controllerBehavioursOptions = new ControllerBehavioursOptions($options);
    }

    /**
     * @return ControllerBehavioursOptions
     */
    public function getControllerBehaviours()
    {
        return $this->controllerBehavioursOptions;
    }

    /**
     * @param  array $options
     *
     * @return void
     */
    public function setResourceMetadata(array $options)
    {
        $this->resourceMetadataOptions = new ResourceMetadataOptions($options);
    }

    /**
     * @return ResourceMetadataOptions
     */
    public function getResourceMetadata()
    {
        return $this->resourceMetadataOptions;
    }

    /**
     * @param  array $decoders
     *
     * @return void
     */
    public function setDecoders(array $decoders)
    {
        $this->decoders = $decoders;
    }

    /**
     * @return array
     */
    public function getDecoders()
    {
        return $this->decoders;
    }

    /**
     * @param array $models
     *
     * @return void
     */
    public function setModels(array $models)
    {
        $this->models = $models;
    }

    /**
     * @return array
     */
    public function getModels()
    {
        return $this->models;
    }
}
