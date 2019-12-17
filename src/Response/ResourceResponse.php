<?php
/**
 * @author    Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Response;

use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Resource response
 */
class ResourceResponse extends StreamedResponse
{
    /**
     * @param Resource $resource
     * @param integer  $status
     * @param array    $headers
     */
    public function __construct($resource, int $status = self::HTTP_OK, array $headers = [])
    {
        $streamer = function () use ($resource) {
            stream_copy_to_stream(
                $resource,
                fopen('php://output', 'wb')
            );
        };

        parent::__construct($streamer, $status, $headers);
    }
}
