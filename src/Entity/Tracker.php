<?php
/**
 * @author      Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright   Copyright (c) 2019, Darvin Studio
 * @link        https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Entity\Security\ApiUser;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * Class Tracker
 *
 * @ORM\Entity(repositoryClass="App\Repository\TrackerRepository")
 *
 * @JMS\ExclusionPolicy("ALL")
 */
class Tracker
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     * @ORM\Id()
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"tracker_list", "tracker_show"})
     */
    protected $id;

    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="datetime_immutable")
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"tracker_list", "tracker_show"})
     */
    protected $startedAt;

    /**
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="trackers")
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"tracker_show"})
     *
     * @SWG\Property(ref="#/definitions/ProjectFromList")
     */
    protected $project;

    /**
     * @var ArrayCollection|ApiUser[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Security\ApiUser", cascade={"persist"})
     * @ORM\JoinTable(name="users_trackers",
     *      joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     * )
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"tracker_show"})
     *
     * @SWG\Property(type="array", @SWG\Items(ref="#/definitions/UserFromList"))
     */
    protected $developers;

    /**
     * @var array|null
     *
     * @ORM\Column(type="json_array", nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"tracker_show"})
     */
    protected $links;

    /**
     * @var ArrayCollection|Bug[]
     *
     * @ORM\OneToMany(targetEntity="Bug", mappedBy="tracker", cascade={"persist", "remove"})
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"tracker_show"})
     *
     * @SWG\Property(ref="#/definitions/Bug")
     */
    protected $bugs;

    /**
     * @var ApiUser
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\ApiUser")
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"tracker_list", "tracker_show"})
     *
     * @SWG\Property(ref="#/definitions/UserFromList")
     */
    protected $author;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"tracker_list", "tracker_show"})
     */
    protected $closed;

    /**
     * Tracker constructor.
     *
     * @param ApiUser $author
     * @param Project $project
     * @param array   $developers
     * @param array   $links
     *
     * @throws \Exception
     */
    public function __construct(ApiUser $author, Project $project, array $developers = [], array $links = [])
    {
        $this->author     = $author;
        $this->project    = $project;
        $this->developers = new ArrayCollection($developers);
        $this->links      = $links;
        $this->closed     = false;
        $this->startedAt  = new \DateTimeImmutable();
        $this->bugs       = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getStartedAt(): \DateTimeImmutable
    {
        return $this->startedAt;
    }

    /**
     * @return Project
     */
    public function getProject(): Project
    {
        return $this->project;
    }

    /**
     * @return \App\Entity\Security\ApiUser[]|\Doctrine\Common\Collections\ArrayCollection
     */
    public function getDevelopers(): ArrayCollection
    {
        return $this->developers;
    }

    /**
     * @param ApiUser $developer
     */
    public function addDeveloper(ApiUser $developer): void
    {
        if (!$this->developers->contains($developer)) {
            $this->developers->add($developer);
        }
    }

    /**
     * @param ApiUser $developer
     */
    public function removeDeveloper(ApiUser $developer): void
    {
        $this->developers->removeElement($developer);
    }

    /**
     * @param Bug $bug
     */
    public function addBug(Bug $bug): void
    {
        $this->bugs->add($bug);
    }

    /**
     * @param Bug $bug
     */
    public function removeBug(Bug $bug): void
    {
        $this->bugs->removeElement($bug);
    }

    public function close()
    {
        $this->closed = true;
    }

    public function open()
    {
        $this->closed = false;
    }
}
