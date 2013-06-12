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

use Doctrine\Common\Collections\Criteria;
use Zend\Http\Request;
use ZfrRest\Mvc\Exception\UnexpectedValueException;
use ZfrRest\Resource\Resource;
use ZfrRest\Resource\ResourceInterface;

/**
 * {@inheritDoc}
 *
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class AssociationSubPathMatcher implements SubPathMatcherInterface
{
    /**
     * {@inheritDoc}
     */
    public function matchSubPath(
        ResourceInterface $resource,
        $subPath,
        Request $request,
        SubPathMatch $previousMatch = null
    ) {
        if ($resource->isCollection()) {
            return null;
        }

        $data = $resource->getData();

        if (! is_object($data)) {
            // unable to handle non-object resources
            return null;
        }

        $resourceMetadata = $resource->getMetadata();
        $associationName  = array_shift(explode('/', trim($subPath, '/')));

        if (! $resourceMetadata->hasAssociation($associationName)) {
            return null;
        }

        $classMetadata       = $resourceMetadata->getClassMetadata();
        $reflectionClass     = $classMetadata->getReflectionClass();
        // @todo Using reflection directly? Maybe should be coded in the metadata interface instead?
        $reflectionProperty  = $reflectionClass->getProperty($associationName);
        $associationMetadata = $resourceMetadata->getAssociationMetadata($associationName);

        $reflectionProperty->setAccessible(true);

        $associationData     = $reflectionProperty->getValue($data);

        if (! $associationMetadata->getClassMetadata()->getReflectionClass()->isInstance($associationData)) {
            throw UnexpectedValueException::unexpectedResourceType($associationMetadata, $associationData);
        }

        return new SubPathMatch(
            new Resource($associationData, $associationMetadata),
            substr($subPath, strpos($subPath, $associationName), strlen($associationName)),
            $previousMatch
        );
    }
}
