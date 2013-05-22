# Quick start

## Associations

ZfrRest can also be used to traverse associations. To illustrate this, let's add an assocation from the User entity
to a new Tweet entity.

Here is the updated User entity:

```php
<?php

namespace Application\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use ZfrRest\Resource\Metadata\Annotation as REST;

/**
 * @ORM\Entity
 * @ORM\Table(name="Users")
 * @REST\Resource(
 *      controller="Application\Controller\UserController",
 *      inputFilter="Application\InputFilter\UserInputFilter"
 * )
 * @REST\Collection(controller="Application\Controller\UserListController")
 */
class User
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $name;

    /**
     * @var Array
     *
     * @ORM\OneToMany(targetEntity="Tweet", mappedBy="user")
     * @REST\Association
     */
    protected $tweets;

    /**
     *
     */
    public function __construct()
    {
        $this->tweets = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return Array|ArrayCollection
     */
    public function getTweets()
    {
        return $this->tweets;
    }
}
```

And the Tweet entity:

```php
<?php

namespace Application\Entity;

use Doctrine\ORM\Mapping as ORM;
use ZfrRest\Resource\Metadata\Annotation as REST;

/**
 * @ORM\Entity
 * @ORM\Table(name="Tweets")
 * @REST\Collection(controller="Application\Controller\TweetListController")
 */
class Tweet
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    protected $id;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="tweets")
     */
    protected $user;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    protected $content;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Application\Entity\User $user
     * @return void
     */
    public function setUser($user)
    {
        $this->user = $user;
    }

    /**
     * @return \Application\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param string $content
     * @return void
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }
}
```

As you can see, the "tweets" properties on the User entity has the annotation "Association". This indicate to the
router that it can recursively traverses a new resource. It therefore opens the following URLs: "/users/:user_id/tweets" and
"/users/:user_id/tweets/:tweet_id".

However, the routes "/tweets" and "/tweets/:tweet_id" are still not accessible (for this to work, you would need to add
a new root route in the config file, like we did earlier for the user.

The new routes use the mapping described in the Tweet entity. For example, the URL GET "/users/4/tweets" will be
dispatched to the controller defined in the Collection mapping of the Tweet entity (in this case, the TweetListController).
However, in some cases, you may want to change the mapping based on the context. This is why you can use both the
Collection and Resource annotations at the association level to override mapping. For instance:

```php
class User
{
    // ...

    /**
     * @var Array
     *
     * @ORM\OneToMany(targetEntity="Tweet", mappedBy="user")
     * @REST\Association
     * @REST\Collection(controller="Application\Controller\UserTweetListController")
     */
    protected $tweets;
}
```

Now, the URL GET "/users/4/tweets" will be dispatched to `Application\Controller\UserTweetListController`.

We're done with the quick start ! For more advanced features, please refer to the [cook-book](../cook-book.md).
