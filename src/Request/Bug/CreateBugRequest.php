<?php
/**
 * @author    Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Request\Bug;

use App\DBAL\Types\BugPriorityType;
use App\Request\JsonRequest;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Create bug request
 */
class CreateBugRequest extends JsonRequest
{
    /**
     * @var string
     *
     * @SWG\Property(type="string")
     */
    protected $title;

    /**
     * @var string
     *
     * @SWG\Property(type="string")
     */
    protected $description;

    /**
     * @var string
     *
     * @SWG\Property(type="string", enum={"critical", "major", "normal", "minor"})
     */
    protected $priority;

    /**
     * @var int
     *
     * @SWG\Property(type="integer")
     */
    protected $responsiblePerson;

    /**
     * @return \Symfony\Component\Validator\Constraint|\Symfony\Component\Validator\Constraint[]|\Symfony\Component\Validator\Constraints\Collection
     */
    public function rules()
    {
        return new Assert\Collection([
            'title'      => new Assert\NotBlank(),
            'description' => new Assert\NotNull(),
            'priority' => new Assert\Choice(['choices' => BugPriorityType::getChoices()]),
            'responsiblePerson' => new Assert\Type(['type' => 'integer'])
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
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getPriority(): string
    {
        return $this->priority;
    }

    /**
     * @return int
     */
    public function getResponsiblePerson(): int
    {
        return $this->responsiblePerson;
    }
}
