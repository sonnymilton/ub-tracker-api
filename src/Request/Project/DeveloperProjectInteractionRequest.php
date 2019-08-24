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

use Fesor\RequestObject\PayloadResolver;
use Fesor\RequestObject\RequestObject;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class DeveloperProjectInteractionRequest
 */
class DeveloperProjectInteractionRequest extends RequestObject implements PayloadResolver
{
    /**
     * @var int
     *
     * @SWG\Property(type="integer")
     */
    protected $developer;

    /**
     * @param Request $request
     *
     * @return array
     */
    public function resolvePayload(Request $request): array
    {
        return [
            'developer' => $this->developer = intval($request->query->get('developer')),
        ];
    }

    /**
     * @return \Symfony\Component\Validator\Constraint|\Symfony\Component\Validator\Constraint[]|Assert\Collection
     */
    public function rules(): Assert\Collection
    {
        return new Assert\Collection([
            'developer' => [
                new Assert\NotNull(),
                new Assert\Type('integer'),
            ],
        ]);
    }

    /**
     * @return int
     */
    public function getDeveloper(): int
    {
        return $this->developer;
    }
}
