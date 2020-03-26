<?php
/**
 * @author    Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Request\Tracker;

use App\Entity\Security\ApiUser;
use App\Request\JsonRequest;
use App\Request\HasResolvableEntitiesInterface;
use App\Request\ResolvableTrait;
use App\Request\UnableToProcessRequestObjectException;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Create tracker request
 */
class TrackerRequest extends JsonRequest implements HasResolvableEntitiesInterface
{
    use ResolvableTrait;

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
     * @var array|\App\Entity\Security\ApiUser[]
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
            'developers' => new Assert\Optional([
                new Assert\NotNull(),
                new Assert\Type('array'),
                new Assert\Count(['min' => 1]),
                new Assert\All([
                    new Assert\Type('integer'),
                    new Assert\GreaterThanOrEqual(0),
                ]),
            ]),
            'links'      => new Assert\Optional([
                new Assert\NotNull(),
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
     * @inheritDoc
     */
    function resolve(EntityManagerInterface $entityManager): void
    {
        if (null === $this->developers) {
            $this->resolved   = true;
            $this->developers = [];

            return;
        }

        $userRepository   = $entityManager->getRepository(ApiUser::class);
        $expectedCount    = count($this->developers);
        $this->developers = $userRepository->getUsersByIds($this->developers);

        if ($expectedCount !== count($this->developers)) {
            throw new NotFoundHttpException('Developer(s) not found');
        }

        foreach ($this->developers as $user) {
            if (!$user->isDeveloper()) {
                throw new UnableToProcessRequestObjectException($this, 'Specified user(s) are not developer(s)');
            }
        }

        $this->resolved = true;
    }

    /**
     * @return array|null
     */
    public function getLinks(): array
    {
        return $this->links ?? [];
    }

    /**
     * @return array|\App\Entity\Security\ApiUser[]
     */
    public function getDevelopers(): array
    {
        $this->suppressIfNotResolved();

        return $this->developers;
    }
}
