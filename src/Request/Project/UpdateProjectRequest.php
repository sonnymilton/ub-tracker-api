<?php
/**
 * @author      Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright   Copyright (c) 2019, Darvin Studio
 * @link        https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Request\Project;

use App\Entity\Project\Links;
use App\Request\JsonRequest;
use Nelmio\ApiDocBundle\Annotation\Model;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Update project request
 */
class UpdateProjectRequest extends JsonRequest
{
    /**
     * @var string
     *
     * @SWG\Property(type="string")
     */
    protected $title;

    /**
     * @var \App\Entity\Project\Links
     *
     * @SWG\Property(ref=@Model(type=Links::class))
     */
    protected $links;

    /**
     * @return \Symfony\Component\Validator\Constraint|\Symfony\Component\Validator\Constraint[]|Assert\Collection
     */
    public function rules(): Assert\Collection
    {
        return new Assert\Collection([
            'title' => new Assert\NotBlank(),
            'links' => new Assert\Optional([
                new Assert\Collection([
                    'task'       => new Assert\Optional(
                        new Assert\Url()
                    ),
                    'repository' => new Assert\Optional(
                        new Assert\Url()
                    ),
                    'liveSite'   => new Assert\Optional(
                        new Assert\Url()
                    ),
                    'testSite'   => new Assert\Optional(
                        new Assert\Url()
                    ),
                ]),
            ]),
        ]);
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * @return \App\Entity\Project\Links|null
     */
    public function getLinks(): ?Links
    {
        return $this->links;
    }
}
