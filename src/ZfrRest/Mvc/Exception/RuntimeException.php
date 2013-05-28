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

namespace ZfrRest\Mvc\Exception;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use RuntimeException as BaseRuntimeException;
use ZfrRest\Exception\ExceptionInterface;

/**
 * RuntimeException
 *
 * @license MIT
 * @author  Michaël Gallego <mic.gallego@gmail.com>
 */
class RuntimeException extends BaseRuntimeException implements ExceptionInterface
{
    /**
     * @param ClassMetadata $classMetadata
     *
     * @return self
     */
    public static function missingCollectionMetadata(ClassMetadata $classMetadata)
    {
        return new self(
            sprintf(
                'Collection metadata not found. Do you have a @Collection annotation for the resource "%s"?',
                $classMetadata->getName()
            )
        );
    }

    /**
     * @param mixed $resource
     *
     * @return self
     */
    public static function unsupportedResourceType($resource)
    {
        return new self(
            sprintf(
                'Resource "%s" is not supported: either specify an ObjectRepository instance, or an entity class name',
                is_object($resource) ? get_class($resource) : gettype($resource)
            )
        );
    }
}
