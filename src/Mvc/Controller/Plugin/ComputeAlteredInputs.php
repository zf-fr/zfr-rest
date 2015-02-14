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

use Zend\InputFilter\InputFilterPluginManager;
use Zend\Mvc\Controller\Plugin\AbstractPlugin;
use ZfrRest\Http\Exception\Client\UnprocessableEntityException;

/**
 * @author  Kristofer Karlsson <karlsson.kristofer@gmail.com>
 * @licence MIT
 */
class ComputeAlteredInputs extends AbstractPlugin
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
     * Compute altered inputs from incoming data
     *
     * @param  string $inputFilterName
     * @param  object $object
     *
     * @return array
     * @throws UnprocessableEntityException
     */
    public function __invoke($inputFilterName, $object)
    {
        /** @var \Zend\InputFilter\InputFilterInterface $inputFilter */
        $inputFilter = $this->inputFilterPluginManager->get($inputFilterName);
        $data        = json_decode($this->controller->getRequest()->getContent(), true) ?: [];
        $inputs      = array_keys(array_diff($data, (array)$object));

        foreach ($inputs as $input) {
            if (!$inputFilter->has($input)) {
                throw new UnprocessableEntityException('Unrecognized input \'' . $input . '\'');
            }
        }

        return $inputs;
    }
}
