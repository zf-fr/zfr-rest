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

namespace ZfrRest\Serializer;

use Zend\ServiceManager\AbstractPluginManager;
use Symfony\Component\Serializer\Encoder\DecoderInterface;

/**
 * DecoderPluginManager
 *
 * @license MIT
 * @since   0.0.1
 */
class DecoderPluginManager extends AbstractPluginManager
{
    /**
     * @var array
     */
    protected $invokableClasses = array(
        'application/json'       => 'Symfony\Component\Serializer\Encoder\JsonDecode',
        'application/javascript' => 'Symfony\Component\Serializer\Encoder\JsonDecode',
        'application/xml'        => 'Symfony\Component\Serializer\Encoder\XmlEncoder'
    );

    /**
     * {@inheritDoc}
     */
    public function validatePlugin($plugin)
    {
        if ($plugin instanceof DecoderInterface) {
            return;
        }

        throw new Exception\RuntimeException(sprintf(
            'Plugin of type %s is invalid; must implement Symfony\Component\Serializer\Encoder\DecoderInterface',
            (is_object($plugin) ? get_class($plugin) : gettype($plugin))
        ));
    }
}
