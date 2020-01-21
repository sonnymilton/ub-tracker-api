<?php
/**
 * @author    Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity;

use App\Entity\BugReport\BugReport;
use App\Entity\Security\ApiUser;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;
use Swagger\Annotations as SWG;

/**
 * Comment
 *
 * @ORM\Entity(repositoryClass="App\Repository\CommentRepository")
 *
 * @JMS\ExclusionPolicy("ALL")
 */
class Comment
{
    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue()
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"comment_list", "comment_details"})
     */
    protected $id;

    /**
     * @var \App\Entity\Security\ApiUser
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\Security\ApiUser")
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"comment_list", "comment_details"})
     *
     * @SWG\Property(ref="#/definitions/UserFromList")
     */
    protected $author;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"comment_list", "comment_details"})
     */
    protected $text;

    /**
     * @var \DateTimeImmutable
     *
     * @ORM\Column(type="datetime_immutable")
     *
     * @JMS\Expose()
     * @JMS\Groups(groups={"comment_list", "comment_details"})
     */
    protected $createdAt;

    /**
     * @var \App\Entity\BugReport\BugReport
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\BugReport\BugReport", inversedBy="comments")
     */
    protected $bugReport;

    /**
     * Comment constructor.
     *
     * @param \App\Entity\Security\ApiUser    $author
     * @param \App\Entity\BugReport\BugReport $bugReport
     * @param string                          $text
     *
     * @throws \Exception
     */
    public function __construct(ApiUser $author, BugReport $bugReport, string $text)
    {
        $this->author    = $author;
        $this->bugReport = $bugReport;
        $this->text      = $text;
        $this->createdAt = new DateTimeImmutable();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return \App\Entity\Security\ApiUser
     */
    public function getAuthor(): ApiUser
    {
        return $this->author;
    }

    /**
     * @return string
     */
    public function getText(): string
    {
        return $this->text;
    }

    /**
     * @return \DateTimeImmutable
     */
    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    /**
     * @param string $text
     */
    public function update(string $text)
    {
        $this->text = $text;
    }
}
