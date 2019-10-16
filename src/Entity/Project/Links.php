<?php
/**
 * @author    Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Entity\Project;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as JMS;

/**
 * Links
 *
 * @ORM\Embeddable()
 *
 * @JMS\ExclusionPolicy("ALL")
 */
class Links
{
    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups(groups={"project_details"})
     */
    protected $task;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups(groups={"project_details"})
     */
    protected $repository;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups(groups={"project_details"})
     */
    protected $liveSite;

    /**
     * @var string|null
     *
     * @ORM\Column(nullable=true)
     *
     * @JMS\Expose()
     * @JMS\Type("string")
     * @JMS\Groups(groups={"project_details"})
     */
    protected $testSite;

    /**
     * Links constructor.
     *
     * @param null|string $task
     * @param null|string $repository
     * @param null|string $liveSite
     * @param null|string $testSite
     */
    public function __construct(?string $task, ?string $repository, ?string $liveSite, ?string $testSite)
    {
        $this->task       = $task;
        $this->repository = $repository;
        $this->liveSite   = $liveSite;
        $this->testSite   = $testSite;
    }

    /**
     * @param array $array
     *
     * @return \App\Entity\Project\Links
     */
    public static function createFromArray(array $array): Links
    {
        $links = new self(null, null, null, null);

        foreach ($array as $key => $value) {
            $links->{$key} = $value;
        }

        return $links;
    }

    /**
     * @return null|string
     */
    public function getTask(): ?string
    {
        return $this->task;
    }

    /**
     * @return null|string
     */
    public function getRepository(): ?string
    {
        return $this->repository;
    }

    /**
     * @return null|string
     */
    public function getLiveSite(): ?string
    {
        return $this->liveSite;
    }

    /**
     * @return null|string
     */
    public function getTestSite(): ?string
    {
        return $this->testSite;
    }
}
