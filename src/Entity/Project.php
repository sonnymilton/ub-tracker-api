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
use App\Request\Project\UpdateProjectRequest;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

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
     *
     * @SWG\Property(ref="#/definitions/TrackerFromList")
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
     *
     * @SWG\Property(type="array", @SWG\Items(ref="#/definitions/UserFromList"))
     */
    protected $developers;

    /**
     * @var ApiUser
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\ApiUser", cascade={"persist"})
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"project_details"})
     *
     * @SWG\Property(ref="#/definitions/UserFromList")
     */
    protected $author;

    /**
     * @var array|null
     *
     * @ORM\Column(type="json_array")
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"project_details"})
     */
    protected $links;

    /**
     * @var array|string[]
     *
     * @ORM\Column(type="simple_array")
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"project_details"})
     */
    protected $locales;

    /**
     * Project constructor.
     *
     * @param ApiUser $author
     * @param string  $title
     * @param array   $locales
     * @param array   $links
     *
     * @throws \Exception
     */
    public function __construct(ApiUser $author, string $title, array $locales, array $links = null)
    {
        $this->author     = $author;
        $this->title      = $title;
        $this->createdAt  = new \DateTimeImmutable();
        $this->trackers   = new ArrayCollection();
        $this->developers = new ArrayCollection();
        $this->links      = $links;
        $this->locales    = $locales;
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
     * @return ApiUser[]|Collection
     */
    public function getDevelopers(): Collection
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
     * @return array|null
     */
    public function getLinks(): ?array
    {
        return $this->links;
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
     * @param \App\Request\Project\UpdateProjectRequest $request
     */
    public function updateFromRequest(UpdateProjectRequest $request)
    {
        $this->title   = $request->getTitle();
        $this->links   = $request->getLinks();
        $this->locales = $request->getLocales();
    }
}
