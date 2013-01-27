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

namespace ZfrRest\Resource\Exception;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use UnexpectedValueException as BaseUnexpectedValueException;

/**
 * @license MIT
 * @author  Marco Pivetta <ocramius@gmail.com>
 */
class UnexpectedValueException extends BaseUnexpectedValueException
{
    /**
     * @param mixed                                              $resource
     * @param \Doctrine\Common\Persistence\Mapping\ClassMetadata $metadata
     *
     * @return \ZfrRest\Resource\Exception\UnexpectedValueException
     */
    public static function invalidResourceException($resource, ClassMetadata $metadata)
    {
        return new static(
            sprintf(
                'A resource of type "%s" was expected, "%s" given',
                $metadata->getName(),
                is_object($resource) ? get_class($resource) : gettype($resource)
            )
        );
    }

    /**
     * @param mixed $value
     *
     * @return \ZfrRest\Resource\Exception\UnexpectedValueException
     */
    public static function nonMatchableCollection($value)
    {
        return new static(
            sprintf(
                'Retrieved collection of type "%s" cannot be filtered (not a Selectable)',
                is_object($value) ? get_class($value) : gettype($value)
            )
        );
    }

    /**
     * @param mixed $value
     *
     * @return \ZfrRest\Resource\Exception\UnexpectedValueException
     */
    public static function unknownCollectionType($value)
    {
        return new static(
            sprintf(
                'Unknown collection value "%s" found, expecting array or selectable collection',
                is_object($value) ? get_class($value) : gettype($value)
            )
        );
    }

    /**
     * @param mixed $value
     *
     * @return \ZfrRest\Resource\Exception\UnexpectedValueException
     */
    public static function unknownMetadata($value)
    {
        return new static(
            sprintf(
                'No metadata found for "%s"',
                $value
            )
        );
    }
}
