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

use Zend\EventManager\EventManagerAwareInterface;
use Zend\EventManager\EventManagerInterface;
use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilterPluginManager;
use ZfrRest\Http\Exception\Client\UnprocessableEntityException;
use ZfrRest\Mvc\Controller\Event\ValidationEvent;
use ZfrRest\Mvc\Exception\RuntimeException;
use ZfrRest\Resource\ResourceInterface;

/**
 * This trait is responsible for validating data for any method handler
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
trait DataValidationTrait
{
    /**
     * @var InputFilterPluginManager
     */
    protected $inputFilterPluginManager;

    /**
     * Filter and validate the data
     *
     * @param  ResourceInterface          $resource
     * @param  array                      $data
     * @param  EventManagerAwareInterface $controller
     * @return array
     * @throws RuntimeException If no input filter is bound to the resource
     * @throws UnprocessableEntityException If validation fails
     */
    public function validateData(ResourceInterface $resource, array $data, EventManagerAwareInterface $controller)
    {
        /* @var EventManagerInterface $eventManager */
        $eventManager = $controller->getEventManager();

        $event = new ValidationEvent($resource, $this->inputFilterPluginManager);
        $event->setTarget($controller);

        $eventManager->trigger(ValidationEvent::EVENT_VALIDATE_PRE, $event);

        if (!$event->getAutoValidate()) {
            return $data;
        }

        /* @var InputFilterInterface $inputFilter */
        $inputFilter = $event->getInputFilter();

        if (!$inputFilter instanceof InputFilterInterface) {
            if (!($inputFilterName = $resource->getMetadata()->getInputFilterName())) {
                throw new RuntimeException('No input filter name has been found in resource metadata');
            }

            $inputFilter = $this->inputFilterPluginManager->get($inputFilterName);

            $event->setInputFilter($inputFilter);
        }

        $inputFilter->setData($data);

        if (!$inputFilter->isValid($resource->getData())) {
            $eventManager->trigger(ValidationEvent::EVENT_VALIDATE_ERROR, $event);

            throw new UnprocessableEntityException(
                'Validation error',
                $this->extractErrorMessages($inputFilter)
            );
        }

        $eventManager->trigger(ValidationEvent::EVENT_VALIDATE_SUCCESS, $event);

        return $inputFilter->getValues();
    }

    /**
     * Extract error messages from the input filter
     *
     * @param  InputFilterInterface $inputFilter
     * @return array
     */
    protected function extractErrorMessages(InputFilterInterface $inputFilter)
    {
        $errorMessages = $inputFilter->getMessages();

        array_walk($errorMessages, function (&$value, $key) use ($inputFilter) {
            if ($inputFilter->has($key) && $inputFilter->get($key) instanceof InputFilterInterface) {
                $value = $this->extractErrorMessages($inputFilter->get($key));
            } else {
                $value = array_values($value);
            }
        });

        return $errorMessages;
    }
}
