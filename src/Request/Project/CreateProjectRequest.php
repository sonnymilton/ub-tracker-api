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


use App\Entity\Security\ApiUser;
use App\Request\JsonRequest;
use Swagger\Annotations as SWG;
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
     * @var ApiUser[]
     *
     * @SWG\Property(
     *     type="array",
     *     @SWG\Items(type="integer"),
     *     description="Developers IDs. Optional",
     * )
     */
    protected $developers;

    /**
     * @return \Symfony\Component\Validator\Constraint|\Symfony\Component\Validator\Constraint[]|Assert\Collection
     */
    public function rules(): Assert\Collection
    {
        return new Assert\Collection([
            'title' => new Assert\NotBlank(),
            'developers' => new Assert\Optional(([
                new Assert\Type('array'),
                new Assert\Count(['min' => 1]),
                new Assert\All([
                    new Assert\Type('integer'),
                    new Assert\GreaterThanOrEqual(0)
                ])
            ]))
        ]);
    }
}
