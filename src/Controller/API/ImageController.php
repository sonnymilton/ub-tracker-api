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
use Swagger\Annotations as SWG;
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
 *
 * @SWG\Tag(name="Image")
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
     * @Route("/", name="upload", methods={"POST"})
     *
     * @param \App\Request\Image\ImageRequest $request
     *
     * @SWG\Parameter(
     *     name="image",
     *     in="formData",
     *     type="file"
     * )
     *
     * @SWG\Response(
     *     response="201",
     *     description="Uploads image",
     *     @SWG\Schema(properties={
     *          @SWG\Property(property="id", type="string"),
     *          @SWG\Property(property="resource", type="string", format="url")
     *     })
     * )
     * @SWG\Response(
     *     response="400",
     *     description="Invalid request data."
     * )
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

        $stream = fopen($image->getRealPath(), 'r');
        $path   = uniqid();

        $this->filesystem->writeStream($path, $stream, [
            'mimetype' => $image->getMimeType(),
        ]);

        return new JsonResponse([
            'id'       => $path,
            'resource' => $this->generateUrl('api_image_show', ['id' => $path], UrlGeneratorInterface::ABSOLUTE_URL),
        ]);
    }

    /**
     * @Route("/{id}/", name="show", methods={"GET"})
     *
     * @param string $id
     *
     * @SWG\Response(
     *     response="200",
     *     description="Returns image by id"
     * )
     * @SWG\Response(
     *     response="404",
     *     description="Image not found"
     * )
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
