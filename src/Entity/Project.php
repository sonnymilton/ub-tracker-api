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

/**
 * Class Project
 *
 * @ORM\Entity(repositoryClass="App\Repository\ProjectRepository")
 *
 * @JMS\ExclusionPolicy("ALL")
 */
class Project
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     * @ORM\Id()
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"project_list", "project_details"})
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column()
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"project_list", "project_details"})
     */
    protected $title;

    /**
     * @var ArrayCollection|Tracker[]
     *
     * @ORM\OneToMany(targetEntity="Tracker", mappedBy="project", cascade={"persist", "remove"})
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"project_details"})
     */
    protected $trackers;

    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="datetime_immutable")
     *
     * @JMS\Expose()
     * @JMS\Type("DateTimeImmutable")
     * @JMS\Groups(groups={"project_list", "project_details"})
     *
     */
    protected $createdAt;

    /**
     * @var ArrayCollection|ApiUser[]
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Security\ApiUser", cascade={"persist"})
     * @ORM\JoinTable(name="users_projects",
     *      joinColumns={@ORM\JoinColumn(name="project_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="user_id", referencedColumnName="id")}
     * )
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"project_details"})
     */
    protected $developers;

    /**
     * @var ApiUser
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\ApiUser", cascade={"persist"})
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"project_details"})
     */
    protected $author;

    /**
     * Project constructor.
     *
     * @param ApiUser $author
     * @param string  $title
     * @param array   $developers
     *
     * @throws \Exception
     */
    public function __construct(ApiUser $author, string $title, array $developers = null)
    {
        $this->author     = $author;
        $this->title      = $title;
        $this->createdAt  = new \DateTimeImmutable();
        $this->trackers   = new ArrayCollection();
        $this->developers = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return Tracker[]|ArrayCollection
     */
    public function getTrackers(): ArrayCollection
    {
        return $this->trackers;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param string $title
     */
    public function changeTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * @param Tracker $tracker
     */
    public function addTracker(Tracker $tracker): void
    {
        $this->trackers->add($tracker);
    }

    /**
     * @param Tracker $tracker
     */
    public function removeTracker(Tracker $tracker): void
    {
        $this->trackers->removeElement($tracker);
    }

    /**
     * @return ApiUser[]|ArrayCollection
     */
    public function getDevelopers(): ArrayCollection
    {
        return $this->developers;
    }

    /**
     * @return ApiUser
     */
    public function getAuthor(): ApiUser
    {
        return $this->author;
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
}
