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

use App\Request\JsonRequest;
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
     * @return \Symfony\Component\Validator\Constraint|\Symfony\Component\Validator\Constraint[]|Assert\Collection
     */
    public function rules(): Assert\Collection
    {
        return new Assert\Collection([
            'title' => new Assert\NotBlank(),
        ]);
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->title;
    }
}
