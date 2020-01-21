<?php
/**
 * @author    Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Request;

use Fesor\RequestObject\RequestObject;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Throwable;

/**
 * Unable to process request object exception
 */
final class UnableToProcessRequestObjectException extends UnprocessableEntityHttpException
{
    /**
     * @var RequestObject
     */
    private $requestObject;

    /**
     * UnableToProcessRequestObjectException constructor.
     *
     * @param \Fesor\RequestObject\RequestObject $requestObject
     * @param string|null                        $message
     * @param \Throwable|null                    $previous
     * @param int                                $code
     * @param array                              $headers
     */
    public function __construct(RequestObject $requestObject, string $message = null, Throwable $previous = null, int $code = 0, array $headers = [])
    {
        $this->requestObject = $requestObject;
        parent::__construct($message, $previous, $code, $headers);
    }

    /**
     * @return \Fesor\RequestObject\RequestObject
     */
    public function getRequestObject(): RequestObject
    {
        return $this->requestObject;
    }
}
