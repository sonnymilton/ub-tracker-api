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
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class CreateProjectRequest
 */
class CreateProjectRequest extends JsonRequest
{
    /**
     * @var string
     *
     * @SWG\Property(type="string", required={"true"})
     */
    protected $title;

    /**
     * @var array|int[]
     *
     * @SWG\Property(
     *     type="array",
     *     @SWG\Items(type="integer"),
     *     description="Developers IDs. Optional",
     * )
     */
    protected $developers;

    /**
     * @var \App\Entity\Project\Links
     *
     * @SWG\Property(ref=@Model(type=Links::class))
     */
    protected $links;

    /***
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function resolvePayload(Request $request): array
    {
        $payload = parent::resolvePayload($request);

        $this->links = Links::createFromArray($payload['links']);

        return $payload;
    }

    /**
     * @return \Symfony\Component\Validator\Constraint|\Symfony\Component\Validator\Constraint[]|Assert\Collection
     */
    public function rules(): Assert\Collection
    {
        return new Assert\Collection([
            'title'      => new Assert\NotBlank(),
            'developers' => new Assert\Optional([
                new Assert\Type('array'),
                new Assert\Count(['min' => 1]),
                new Assert\All([
                    new Assert\Type('integer'),
                    new Assert\GreaterThanOrEqual(0),
                ]),
            ]),
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
     * @return array|int[]
     */
    public function getDevelopers(): array
    {
        return $this->developers ?? [];
    }

    /**
     * @return \App\Entity\Project\Links|null
     */
    public function getLinks(): ?Links
    {
        return $this->links;
    }
}
