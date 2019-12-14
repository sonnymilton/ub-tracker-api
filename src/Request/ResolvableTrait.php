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

/**
 * Resolvable trait
 */
trait ResolvableTrait
{
    /**
     * @var bool
     */
    protected $resolved;

    /**
     * ResolvableTrait constructor.
     */
    public function __construct()
    {
        $this->resolved = false;
    }

    /**
     * @return bool
     */
    public function isResolved(): bool
    {
        return $this->resolved;
    }

    private function suppressIfNotResolved(): void
    {
        if (!$this->resolved) {
            throw new RequestObjectIsNotResolvedException($this);
        }
    }
}
