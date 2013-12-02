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

namespace ZfrRest\Stdlib\Hydrator;

use Doctrine\Common\Collections\Collection;
use Zend\Paginator\Paginator;
use DoctrineModule\Stdlib\Hydrator\DoctrineObject;

class ZfrRestHydrator extends DoctrineObject
{

    /**
     * Extract values from an object using a by-value logic (this means that it uses the entity
     * API, in this case, getters)
     *
     * @param  object $object
     * @throws RuntimeException
     * @return array
     */
    protected function extractByValue($object)
    {
        $fieldNames = array_merge($this->metadata->getFieldNames(), $this->metadata->getAssociationNames());
        $methods    = get_class_methods($object);

        $data = array();
        foreach ($fieldNames as $fieldName) {
            $getter = 'get' . ucfirst($fieldName);
            $isser  = 'is' . ucfirst($fieldName);

            if (in_array($getter, $methods)) {
                $data[$fieldName] = $this->extractValue($fieldName, $object->$getter(), $object);
                $data[$fieldName] = $this->extractDepth($fieldName, $data);
            } elseif (in_array($isser, $methods)) {
                $data[$fieldName] = $this->extractValue($fieldName, $object->$isser(), $object);
                $data[$fieldName] = $this->extractDepth($fieldName, $data);
            }

            // Unknown fields are ignored
        }

        return $data;
    }

    protected function extractDepth($fieldName, $data)
    {
        if (in_array($fieldName, $this->metadata->getAssociationNames())) {
            if ($data[$fieldName] instanceof Collection) {
                $extractedValueArray = array();
                foreach($data[$fieldName] as $entity) {
                    $extractedValueArray[] = $this->extractByValue($entity);
                }
                $data[$fieldName] = $extractedValueArray;
            } else {
                $data[$fieldName] = $this->extractByValue($data[$fieldName]);
            }
        }
        return $data[$fieldName];
    }
}
