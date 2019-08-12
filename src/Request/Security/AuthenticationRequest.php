<?php
/**
 * @author      Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright   Copyright (c) 2019, Darvin Studio
 * @link        https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace App\Request\Security;


use App\Request\JsonRequest;
use Fesor\RequestObject\ErrorResponseProvider;
use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Class AuthenticationRequest
 */
class AuthenticationRequest extends JsonRequest implements ErrorResponseProvider
{
    /**
     * @var string
     *
     * @SWG\Property(type="string")
     */
    protected $code;

    /**
     * @return \Symfony\Component\Validator\Constraint|\Symfony\Component\Validator\Constraint[]|Assert\Collection
     */
    public function rules(): Assert\Collection
    {
        return new Assert\Collection([
           'code' =>  new Assert\NotNull(['message' => 'security.auth.code_required'])
        ]);
    }

    /**
     * @param ConstraintViolationListInterface $errors
     *
     * @return Response
     */
    public function getErrorResponse(ConstraintViolationListInterface $errors): Response
    {
        return new Response(null, Response::HTTP_FORBIDDEN);
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->code;
    }
}
