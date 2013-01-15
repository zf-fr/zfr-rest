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

namespace ZfrRest\Options;

use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Zend\Stdlib\AbstractOptions;

/**
 * SerializerOptions
 *
 * @license MIT
 * @since   0.0.1
 */
class SerializerOptions extends AbstractOptions
{
    /**
     * @var array
     */
    protected $encoders;

    /**
     * @var array
     */
    protected $normalizers;


    /**
     * Set the list of available encoders
     *
     * @param  array $encoders
     * @throws Exception\InvalidArgumentException
     * @return SerializerOptions
     */
    public function setEncoders(array $encoders)
    {
        foreach ($encoders as $type => $encoder) {
            if (is_string($encoder) && class_exists($encoder)) {
                $encoder = new $encoder();
            }

            if (!$encoder instanceof EncoderInterface || !$encoder instanceof DecoderInterface) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Encoder must implement both Symfony\Component\Serializer\Encoder\DecoderInterface and
                     Symfony\Component\Serializer\Encoder\DecoderInterface, %s given',
                    get_class($encoder)
                ));
            }

            $this->encoders[$type] = $encoder;
        }

        return $this;
    }

    /**
     * Get the list of available encoders
     *
     * @return array
     */
    public function getEncoders()
    {
        return $this->encoders;
    }

    /**
     * Set the list of available normalizers
     *
     * @param  array $normalizers
     * @throws Exception\InvalidArgumentException
     * @return SerializerOptions
     */
    public function setNormalizers(array $normalizers)
    {
        foreach ($normalizers as $type => $normalizer) {
            if (is_string($normalizer) && class_exists($normalizer)) {
                $normalizer = new $normalizer();
            }

            if (!$normalizer instanceof NormalizerInterface) {
                throw new Exception\InvalidArgumentException(sprintf(
                    'Normalizer must implement Symfony\Component\Serializer\Encoder\NormalizerInterface, %s given',
                    get_class($normalizer)
                ));
            }

            $this->normalizers[$type] = $normalizer;
        }

        return $this;
    }

    /**
     * Get the list of available normalizers
     *
     * @return array
     */
    public function getNormalizers()
    {
        return $this->normalizers;
    }
}
