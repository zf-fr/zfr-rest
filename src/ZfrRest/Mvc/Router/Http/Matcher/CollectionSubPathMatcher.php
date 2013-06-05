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

namespace ZfrRest\Mvc\Router\Http\Matcher;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Selectable;
use Zend\Http\Request;
use Zend\Stdlib\ArrayUtils;
use ZfrRest\Mvc\Exception;
use ZfrRest\Mvc\Exception\RuntimeException;
use ZfrRest\Resource\Resource;
use ZfrRest\Resource\ResourceInterface;

/**
 * {@inheritDoc}
 *
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class CollectionSubPathMatcher implements SubPathMatcherInterface
{
    public function matchSubPath(
        ResourceInterface $resource,
        $subPath,
        Request $request
    ) {
        if (! $resource->isCollection()) {
            return null;
        }

        $path = trim($subPath, '/');

        if (empty($path)) {
            return new SubPathMatch($this->filterAssociation($resource, $request), $subPath);
        }

        $identifier    = array_shift(explode('/', trim($subPath, '/')));
        $classMetadata = $resource->getMetadata()->getClassMetadata();
        $data          = $this->findItem($resource->getData(), $classMetadata->getIdentifierFieldNames(), $identifier);

        if (null === $data) {
            // @todo is a null value actually valid?
            return null;
        }

        return new SubPathMatch(
            new Resource($data, $resource->getMetadata()),
            substr($subPath, strpos($subPath, $identifier), strlen($identifier))
        );
    }

    protected function findItem($data, array $identifierNames, $identifier)
    {
        if (count($identifierNames) > 1) {
            // @todo Cannot match multiple identifiers for now
            return null;
        }

        if (!$data instanceof Selectable) {
            if (! ($data instanceof \Traversable || is_array($data))) {
                // @todo cannot match on non-selectables?
                return null;
            }

            $data = new ArrayCollection(ArrayUtils::iteratorToArray($data));
        }

        if (! $data instanceof Selectable) {
            // @todo should probably also handle repositories with no Selectable API
            return null;
        }

        return $data->matching(
            new Criteria(Criteria::expr()->eq(current($identifierNames), $identifier))
        )->first();
    }

    protected function filterAssociation(ResourceInterface $resource, Request $request)
    {
        // @todo add collection filtering via GET parameters
        return $resource;
    }
}
