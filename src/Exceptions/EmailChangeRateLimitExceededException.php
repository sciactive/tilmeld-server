<?php namespace Tilmeld\Exceptions;

/**
 * EmailChangeRateLimitExceededException exception.
 *
 * This exception is thrown when a user attempts to change their email address
 * before the rate limit time has passed.
 *
 * @package Tilmeld
 * @license https://www.apache.org/licenses/LICENSE-2.0
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
class EmailChangeRateLimitExceededException extends \Exception {}
