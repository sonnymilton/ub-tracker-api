<?php
/**
 * @author    Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Controller\API;

use App\Request\Image\ImageRequest;
use App\Response\ResourceResponse;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Image controller
 *
 * @Route("/image", name="image_")
 */
class ImageController extends AbstractController
{
    /**
     * @var FilesystemInterface
     */
    protected $filesystem;

    /**
     * ImageController constructor.
     *
     * @param \League\Flysystem\FilesystemInterface $defaultFilesystem
     */
    public function __construct(FilesystemInterface $defaultFilesystem)
    {
        $this->filesystem = $defaultFilesystem;
    }

    /**
     * @Route("/", name="upload")
     *
     * @param \App\Request\Image\ImageRequest $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @throws \League\Flysystem\FileExistsException
     */
    public function uploadImageAction(ImageRequest $request)
    {
        $image = $request->getImage();

        if (!$image instanceof UploadedFile) {
            throw new BadRequestHttpException('Image required');
        }

        $path   = uniqid();
        $stream = fopen($image->getRealPath(), 'r');
        $this->filesystem->writeStream($path, $stream);

        return new JsonResponse([
            'id'       => $path,
            'resource' => $this->generateUrl('api_image_show', ['id' => $path], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
    }

    /**
     * @Route("/{id}/", name="show")
     *
     * @param string $id
     *
     * @return \App\Response\ResourceResponse
     *
     */
    public function showImageAction(string $id)
    {
        try {
            $stream = $this->filesystem->readStream($id);
            $mime   = $this->filesystem->getMimetype($id);
        } catch (FileNotFoundException $e) {
            throw new NotFoundHttpException(sprintf('Image with id %s not found', $id));
        }

        return new ResourceResponse($stream, Response::HTTP_OK, [
            'Content-Type' => $mime,
        ]);
    }
}
