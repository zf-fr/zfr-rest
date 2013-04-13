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
     * {@inheritDoc}
     */
    protected $__strictMode__ = false;

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
     * @param  array $options
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
}
