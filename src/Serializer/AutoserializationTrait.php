<?php
/**
 * @author    Nickolay Mikhaylov <sonny@milton.pro>
 * @copyright Copyright (c) 2019, Darvin Studio
 * @link      https://www.darvin-studio.ru
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Serializer;

use JMS\Serializer\SerializationContext;
use JMS\Serializer\SerializerInterface;

/**
 * Serialization contexts trait
 *
 */
trait AutoserializationTrait
{
    /**
     * @param mixed $subject
     *
     * @return string
     */
    public function autoserialize($subject)
    {
        $constErrorMessage = 'To use autoserialize method in the class %s, the constant %s must be defined in it and initialized with an array of strings.';

        if (!defined('self::LIST_SERIALIZATION_GROUPS') || !is_array(self::LIST_SERIALIZATION_GROUPS)) {
            throw new \LogicException(sprintf($constErrorMessage, __CLASS__, 'LIST_SERIALIZATION_GROUPS'));
        }

        if (!defined('self::DETAILS_SERIALIZATION_GROUPS') || !is_array(self::DETAILS_SERIALIZATION_GROUPS)) {
            throw new \LogicException(sprintf($constErrorMessage, __CLASS__, 'DETAILS_SERIALIZATION_GROUPS'));
        }

        if (!isset($this->serializer) || !$this->serializer instanceof SerializerInterface) {
            throw new \LogicException(sprintf('The property $serializer must be defined in %s class and implement %s.', __CLASS__, SerializerInterface::class));
        }

        $groups = is_iterable($subject) ? self::LIST_SERIALIZATION_GROUPS : self::DETAILS_SERIALIZATION_GROUPS;

        /** @var SerializationContext $serializationContext */
        $serializationContext = SerializationContext::create()->setGroups($groups);

        return $this->serializer->serialize($subject, 'json', $serializationContext);
    }
}
