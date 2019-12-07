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
     * @var array|null
     *
     * @SWG\Property(
     *     type="array",
     *     @SWG\Items(
     *      properties={
     *          @SWG\Property(property="title", type="string"),
     *          @SWG\Property(property="url", type="string", format="uri")
     *      }
     *    )
     * )
     */
    protected $links;

    /**
     * @var array|string[]
     *
     * @SWG\Property(
     *     type="array",
     *     @SWG\Items(type="string"),
     *     description="Locales"
     * )
     */
    protected $locales;

    /**
     * @return \Symfony\Component\Validator\Constraint|\Symfony\Component\Validator\Constraint[]|Assert\Collection
     */
    public function rules(): Assert\Collection
    {
        return new Assert\Collection([
            'title'   => new Assert\NotBlank(),
            'locales' => [
                new Assert\NotNull(),
                new Assert\Type('array'),
                new Assert\Count(['min' => 1]),
                new Assert\All([
                    new Assert\Type('string'),
                    new Assert\NotBlank(),
                ]),
            ],
            'links'   => new Assert\Optional([
                new Assert\Type("array"),
                new Assert\All([
                    new Assert\Collection([
                        'title' => new Assert\NotBlank(),
                        'url'   => [
                            new Assert\NotBlank(),
                            new Assert\Url(),
                        ],
                    ]),
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
     * @return array|null
     */
    public function getLinks(): ?array
    {
        return $this->links;
    }

    /**
     * @return array|string[]
     */
    public function getLocales(): ?array
    {
        return $this->locales;
    }
}
