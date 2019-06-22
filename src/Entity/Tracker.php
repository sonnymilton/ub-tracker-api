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
 * Class Tracker
 *
 * @ORM\Entity(repositoryClass="App\Repository\TrackerRepository")
 */
class Tracker
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
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="datetime_immutable")
     */
    protected $startedAt;

    /**
     * @var Project
     *
     * @ORM\ManyToOne(targetEntity="Project", inversedBy="trackers")
     */
    protected $project;

    /**
     * @var ArrayCollection|Bug[]
     *
     * @ORM\OneToMany(targetEntity="Bug", mappedBy="tracker", cascade={"persist", "remove"})
     */
    protected $bugs;

    /**
     * Tracker constructor.
     *
     * @param Project $project
     *
     * @throws \Exception
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
        $this->startedAt = new \DateTimeImmutable();
        $this->bugs = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \DateTime
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
}
