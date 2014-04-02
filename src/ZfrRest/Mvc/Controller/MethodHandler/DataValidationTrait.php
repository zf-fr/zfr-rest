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
use Zend\Mvc\Controller\AbstractController;
use ZfrRest\Http\Exception\Client\UnprocessableEntityException;
use ZfrRest\Mvc\Controller\AbstractRestfulController;
use ZfrRest\Mvc\Controller\ValidationGroupProviderInterface;
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
     * @param  ResourceInterface         $resource
     * @param  array                     $data
     * @param  AbstractRestfulController $controller
     * @return array
     * @throws RuntimeException If no input filter is bound to the resource
     * @throws UnprocessableEntityException If validation fails
     */
    public function validateData(ResourceInterface $resource, array $data, AbstractRestfulController $controller)
    {
        if (!($inputFilterName = $resource->getMetadata()->getInputFilterName())) {
            throw new RuntimeException('No input filter name has been found in resource metadata');
        }

        /* @var \Zend\InputFilter\InputFilter $inputFilter */
        $inputFilter = $this->inputFilterPluginManager->get($inputFilterName);
        $inputFilter = $controller->configureInputFilter($inputFilter);

        $inputFilter->setData($data);

        if (!$inputFilter->isValid()) {
            throw new UnprocessableEntityException(
                'Validation error',
                $this->formatErrorMessages($inputFilter->getMessages())
            );
        }

        return $inputFilter->getValues();
    }

    /**
     * Format error messages. It removes message keys
     *
     * @param  array $errorMessages
     * @return array
     */
    protected function formatErrorMessages(array $errorMessages)
    {
        return array_map(function ($element) {
            return array_values($element);
        }, $errorMessages);
    }
}
