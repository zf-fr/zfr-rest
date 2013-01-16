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

namespace ZfrRest\Http\Parser;

use Zend\Stdlib\MessageInterface;
use ZfrRest\Serializer\EncoderPluginManager;

/**
 * ParserInterface
 *
 * @license MIT
 * @since   0.0.1
 */
interface ParserInterface
{
    /**
     * Set the encoder plugin manager
     *
     * @param  EncoderPluginManager $pluginManager
     * @return ParserInterface
     */
    public function setEncoderPluginManager(EncoderPluginManager $pluginManager);

    /**
     * Get the encoder plugin manager
     *
     * @return EncoderPluginManager
     */
    public function getEncoderPluginManager();

    /**
     * @param  MessageInterface $message
     * @return mixed
     */
    public function parse(MessageInterface $message);
}
