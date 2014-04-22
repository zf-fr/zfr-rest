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

namespace ZfrRestTest\Asset\Resource\Metadata\Annotation;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use ZfrRest\Resource\Metadata\Annotation as REST;

/**
 * @ORM\Entity
 * @REST\Resource(hydrator="ZfrRestTest\Asset\Hydrator\UserWithRoleHydrator")
 */
class UserWithRoles
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @ORM\Column(type="string")
     */
    protected $username;

    /**
     * @ORM\ManyToMany(targetEntity="Role")
     * @REST\Association(extraction="PASS_THRU")
     */
    protected $roles;

    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    /**
     * @param (int) $id
     */
    public function setId($id)
    {
        $this->id = (int) $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = (string) $username;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param Collection $roles
     * @return void
     */
    public function setRoles(Collection $roles)
    {
        $this->roles->clear();

        foreach ($roles as $role) {
            $this->roles->add($role);
        }
    }

    /**
     * @return ArrayCollection
     */
    public function getRoles()
    {
        return $this->roles;
    }
}
