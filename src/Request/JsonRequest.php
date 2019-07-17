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


use Fesor\RequestObject\PayloadResolver;
use Fesor\RequestObject\RequestObject;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class JsonRequest
 */
class JsonRequest extends RequestObject implements PayloadResolver
{
    /**
     * @param Request $request
     *
     * @return array
     */
    public function resolvePayload(Request $request): array
    {
        return json_decode($request->getContent(), true) ?? [];
    }
}
