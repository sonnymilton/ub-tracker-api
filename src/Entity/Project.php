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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class Project
 *
 * @ORM\Entity(repositoryClass="App\Repository\ProjectRepository")
 */
class Project
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue()
     * @ORM\Id()
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column()
     */
    protected $title;

    /**
     * @var ArrayCollection|Tracker[]
     *
     * @ORM\OneToMany(targetEntity="Tracker", mappedBy="project", cascade={"persist", "remove"})
     */
    protected $trackers;

    /**
     * Project constructor.
     *
     * @param string $title
     */
    public function __construct(string $title)
    {
        $this->title = $title;
        $this->trackers = new ArrayCollection();
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
    public function getTrackers()
    {
        return $this->trackers;
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
}
