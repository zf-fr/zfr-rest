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

namespace ZfrRest\Mvc\Controller\MethodHandler;

use Zend\InputFilter\InputFilterPluginManager;
use Zend\Stdlib\Hydrator\HydratorPluginManager;
use ZfrRest\Options\ControllerBehavioursOptions;
use ZfrRest\Resource\ResourceInterface;

/**
 * Abstract handler for methods that use data (like POST and PUT)
 *
 * @TODO: when porting for ZF3, using traits instead
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
abstract class AbstractDataHandler implements MethodHandlerInterface
{
    /**
     * @var ControllerBehavioursOptions
     */
    protected $controllerBehaviourOptions;

    /**
     * @var InputFilterPluginManager
     */
    protected $inputFilterPluginManager;

    /**
     * @var HydratorPluginManager
     */
    protected $hydratorPluginManager;

    /**
     * Constructor
     *
     * @param ControllerBehavioursOptions $controllerBehavioursOptions
     * @param InputFilterPluginManager    $inputFilterPluginManager
     * @param HydratorPluginManager       $hydratorPluginManager
     */
    public function __construct(
        ControllerBehavioursOptions $controllerBehavioursOptions,
        InputFilterPluginManager $inputFilterPluginManager,
        HydratorPluginManager $hydratorPluginManager
    ) {
        $this->controllerBehaviourOptions = $controllerBehavioursOptions;
        $this->inputFilterPluginManager   = $inputFilterPluginManager;
        $this->hydratorPluginManager      = $hydratorPluginManager;
    }

    /**
     * Filter and validate the data
     *
     * @param  ResourceInterface $resource
     * @param  array $data
     * @return array
     */
    public function validateData(ResourceInterface $resource, array $data)
    {
        if (!$this->controllerBehaviourOptions->getAutoValidate()) {
            return $data;
        }

        if (!($inputFilterName = $resource->getMetadata()->getInputFilterName())) {
            // @TODO: Throw an exception
        }

        $inputFilter = $this->inputFilterPluginManager->get($inputFilterName);
        $inputFilter->setData($data);

        if (!$inputFilter->isValid()) {
            // @TODO: Throw an exception with errors
        }

        // Return validated and filtered values
        return $inputFilter->getValues();
    }

    /**
     * Hydrate the data
     *
     * @param  ResourceInterface $resource
     * @param  array $data
     * @return array
     */
    public function hydrateData(ResourceInterface $resource, array $data)
    {
        if (!$this->controllerBehaviourOptions->getAutoHydrate()) {
            return $data;
        }

        if (!($hydratorName = $resource->getMetadata()->getHydratorName())) {
            // @TODO: Throw an exception
        }

        $hydrator = $this->hydratorPluginManager->get($hydratorName);

        return $hydrator->hydrate($data, $resource->getData());
    }
}
