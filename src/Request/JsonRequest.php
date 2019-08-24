<?php
/**
 * @author      Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright   Copyright (c) 2019, Darvin Studio
 * @link        https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Request;

use Fesor\RequestObject\ErrorResponseProvider;
use Fesor\RequestObject\PayloadResolver;
use Fesor\RequestObject\RequestObject;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Class JsonRequest
 */
abstract class JsonRequest extends RequestObject implements PayloadResolver, ErrorResponseProvider
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function resolvePayload(Request $request): array
    {
        $payload = json_decode($request->getContent(), true) ?? [];

        foreach ($payload as $key => $value) {
            $this->{$key} = $value;
        }

        return $payload;
    }

    /**
     * @param ConstraintViolationListInterface $errors
     *
     * @return Response
     */
    public function getErrorResponse(ConstraintViolationListInterface $errors): Response
    {
        return new JsonResponse([
            'message' => 'Please check your data',
            'errors'  => array_map(function (ConstraintViolation $violation) {
                return [
                    'path'    => $violation->getPropertyPath(),
                    'message' => $violation->getMessage(),
                ];
            }, iterator_to_array($errors)),
        ], 400);
    }
}
