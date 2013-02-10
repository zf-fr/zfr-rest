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

namespace ZfrRest\Resource;

/**
 * Base resource metadata interface - contains data about
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 */
interface ResourceMetadataInterface
{
    /**
     * @return \Doctrine\Common\Persistence\Mapping\ClassMetadata
     */
    public function getClassMetadata();

    /**
     * @return string|null name of the controller
     */
    public function getControllerName();

    /**
     * @return string|null name of the input filter to be used for this resource
     */
    public function getInputFilterName();

    /**
     * @return string|null name of the hydrator to be used for this resource
     */
    public function getHydratorName();

    /**
     * @return array|string[] map of encoders to be used for this resource, indexed by content type
     */
    public function getEncoderNames();

    /**
     * @return array|string[] map of decoders to be used for this resource, indexed by content type
     */
    public function getDecoderNames();

    /**
     * @return array|string[] list of associations that can be traversed on this resource
     */
    public function getAssociations();
}