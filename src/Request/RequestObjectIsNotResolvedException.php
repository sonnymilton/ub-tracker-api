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
use LogicException;
use Throwable;

/**
 * Request is not resolved exception
 */
final class RequestObjectIsNotResolvedException extends LogicException
{
    /**
     * @var RequestObject
     */
    private $requestObject;

    /**
     * RequestObjectIsNotResolvedException constructor.
     *
     * @param \Fesor\RequestObject\RequestObject $requestObject
     * @param int|null                           $code
     * @param \Throwable|null                    $previous
     */
    public function __construct(RequestObject $requestObject, ?int $code = 0, ?Throwable $previous = null)
    {
        $this->requestObject = $requestObject;
        parent::__construct(sprintf('%s must be resolved, before usage', get_class($requestObject)), $code, $previous);
    }
}
