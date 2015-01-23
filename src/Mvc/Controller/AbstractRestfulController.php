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

namespace ZfrRest\Mvc\Controller;

use Zend\Http\Request as HttpRequest;
use Zend\Http\Response as HttpResponse;
use Zend\InputFilter\InputFilterInterface;
use Zend\Mvc\Controller\AbstractController;
use Zend\Mvc\MvcEvent;
use ZfrRest\Exception\RuntimeException;
use ZfrRest\Http\Exception\Client\MethodNotAllowedException;
use ZfrRest\Http\Exception\Client\UnprocessableEntityException;

/**
 * Base RESTful controller
 *
 * Contrary to older versions of ZfrRest, the new controller is much more lightweight, and does a lot less. It
 * basically dispatches the request to a method based on its HTTP verb. For instance, if you are doing a POST
 * request, it will dispatch the request to a method called "post" in your controller
 *
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 * @licence MIT
 */
abstract class AbstractRestfulController extends AbstractController
{
    /**
     * {@inheritDoc}
     */
    public function onDispatch(MvcEvent $event)
    {
        $request = $event->getRequest();

        if (!$request instanceof HttpRequest) {
            throw new RuntimeException('RESTful controller from ZfrRest can only handle HTTP requests');
        }

        // ZfrRest RESTful controller allows usage of action, in order to avoid controller duplication for things
        // that do not map well to REST. It does by checking the ":action" parameter. If present, it will use it
        // like standard action controller
        if ($action = $this->params('action')) {
            $method = strtolower($action) . 'Action';
        } else {
            $method = strtolower($request->getMethod());
        }

        if (!method_exists($this, $method)) {
            throw new MethodNotAllowedException('', null, $this->getAllowedVerbs());
        }

        $routeParameters = $this->params()->fromRoute(null, []);
        unset($routeParameters['controller'], $routeParameters['action']);

        $result = $this->$method($routeParameters);

        $event->setResult($result);
    }

    /**
     * Provides a built-in implementation for the HTTP OPTIONS method
     *
     * It returns the supported methods for the given resource
     *
     * @return HttpResponse
     */
    public function options()
    {
        /** @var HttpResponse $response */
        $response = $this->getResponse();

        $response->setContent('');
        $response->setStatusCode(200);
        $response->getHeaders()->addHeaderLine('Allow', implode(', ', $this->getAllowedVerbs()));

        return $response;
    }

    /**
     * @TODO: when we have the new input filter, we should use named validation group and context
     *
     * @param  string $inputFilterName
     * @param  array  $validationGroup
     * @return array
     * @throws
     */
    protected function validateIncomingData($inputFilterName, array $validationGroup = [])
    {
        /** @var \Zend\InputFilter\InputFilterInterface $inputFilter */
        $inputFilter = $this->serviceLocator->get('InputFilterManager')->get($inputFilterName);

        if (!empty($validationGroup)) {
            $inputFilter->setValidationGroup($validationGroup);
        }

        $data = json_decode($this->request->getContent(), true);
        $inputFilter->setData($data);

        if ($inputFilter->isValid()) {
            return $inputFilter->getValues();
        }

        throw new UnprocessableEntityException('Validation error', $this->extractErrorMessages($inputFilter));
    }

    /**
     * @param  string $hydratorName
     * @param  object $object
     * @param  array  $values
     * @return object
     */
    protected function hydrateData($hydratorName, $object, array $values)
    {
        /** @var \Zend\Stdlib\Hydrator\HydratorInterface $hydrator */
        $hydrator = $this->serviceLocator->get('HydratorManager')->get($hydratorName);
        $object   = $hydrator->hydrate($values, $object);

        return $object;
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

    /**
     * @return array
     */
    private function getAllowedVerbs()
    {
        $genericVerbs = ['delete', 'get', 'head', 'options', 'patch', 'post', 'put'];

        return array_intersect(get_class_methods($this), $genericVerbs);
    }
}
