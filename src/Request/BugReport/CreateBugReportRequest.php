<?php
/**
 * @author    Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Request\BugReport;

use App\DBAL\Types\BugReportPriorityType;
use App\Request\JsonRequest;
use Swagger\Annotations as SWG;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Create bug report request
 */
class CreateBugReportRequest extends JsonRequest
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
     * @var array|string[]|null
     *
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    protected $browsers;

    /**
     * @var array|string[]|null
     *
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    protected $resolutions;

    /**
     * @var array|string[]|null
     *
     * @SWG\Property(type="array", @SWG\Items(type="string"))
     */
    protected $locales;

    /**
     * @return \Symfony\Component\Validator\Constraint|\Symfony\Component\Validator\Constraint[]|\Symfony\Component\Validator\Constraints\Collection
     */
    public function rules()
    {
        return new Assert\Collection([
            'title'             => new Assert\NotBlank(),
            'description'       => new Assert\NotNull(),
            'priority'          => new Assert\Choice(['choices' => BugReportPriorityType::getChoices()]),
            'responsiblePerson' => [
                new Assert\NotNull(),
                new Assert\Type(['type' => 'integer']),
            ],
            'browsers'          => new Assert\Optional([
                new Assert\Type('array'),
                new Assert\All([
                    new Assert\Type('string'),
                    new Assert\NotBlank(),
                ]),
            ]),
            'resolutions'       => new Assert\Optional([
                new Assert\Type('array'),
                new Assert\All([
                    new Assert\Type('string'),
                    new Assert\NotBlank(),
                    new Assert\Regex(['pattern' => '/^\d{3,4}x\d{3,4}/']),
                ]),
            ]),
            'locales'           => new Assert\Optional([
                new Assert\Type('array'),
                new Assert\All([
                    new Assert\Type('string'),
                    new Assert\NotBlank(),
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

    /**
     * @return array|string[]|null
     */
    public function getBrowsers(): ?array
    {
        return $this->browsers;
    }

    /**
     * @return array|string[]|null
     */
    public function getResolutions(): ?array
    {
        return $this->resolutions;
    }

    /**
     * @return array|string[]|null
     */
    public function getLocales(): ?array
    {
        return $this->locales;
    }
}
