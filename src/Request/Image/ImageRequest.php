<?php
/**
 * @author    Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Request\Image;

use App\Request\JsonRequest;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;

class ImageRequest extends JsonRequest
{
    /**
     * @var \Symfony\Component\HttpFoundation\File\UploadedFile
     *
     * @SWG\Property(type="file")
     */
    protected $image;

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return array
     */
    public function resolvePayload(Request $request): array
    {
        $this->image = $request->files->get('image');

        return [
            'image' => $this->image,
        ];
    }

    /**
     * @return \Symfony\Component\Validator\Constraints\Collection
     */
    public function rules(): Assert\Collection
    {
        return new Assert\Collection([
            'image' => [
                new Assert\NotNull(),
                new Assert\Image([
                    'maxSize' => '20M',
                ]),
            ],
        ]);
    }

    /**
     * @return \Symfony\Component\HttpFoundation\File\UploadedFile
     */
    public function getImage(): UploadedFile
    {
        return $this->image;
    }
}
