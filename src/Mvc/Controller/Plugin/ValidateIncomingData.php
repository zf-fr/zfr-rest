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

namespace ZfrRest\Mvc\Controller\Plugin;

use Zend\InputFilter\InputFilterInterface;
use Zend\InputFilter\InputFilterPluginManager;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use ZfrRest\Http\Exception\Client\UnprocessableEntityException;

/**
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
class ValidateIncomingData extends AbstractPlugin
{
    /**
     * @var InputFilterPluginManager
     */
    private $inputFilterPluginManager;

    /**
     * @param InputFilterPluginManager $inputFilterPluginManager
     */
    public function __construct(InputFilterPluginManager $inputFilterPluginManager)
    {
        $this->inputFilterPluginManager = $inputFilterPluginManager;
    }

    /**
     * Get the input filter, and validate the incoming data
     *
     * @TODO: when we have the new input filter, we should use named validation group and context
     *
     * @param  string $inputFilterName
     * @param  array  $validationGroup
     * @param  mixed  $context
     * @return array
     * @throws UnprocessableEntityException
     */
    public function __invoke($inputFilterName, array $validationGroup = [], $context = null)
    {
        /** @var \Zend\InputFilter\InputFilterInterface $inputFilter */
        $inputFilter = $this->inputFilterPluginManager->get($inputFilterName);

        if (!empty($validationGroup)) {
            $inputFilter->setValidationGroup($validationGroup);
        }

        $data = json_decode($this->controller->getRequest()->getContent(), true) ?: [];
        $inputFilter->setData($data);

        if ($inputFilter->isValid($context)) {
            return $inputFilter->getValues();
        }

        throw new UnprocessableEntityException('Validation error', $this->extractErrorMessages($inputFilter));
    }

    /**
     * @param  InputFilterInterface $inputFilter
     * @return array
     */
    private function extractErrorMessages(InputFilterInterface $inputFilter)
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
