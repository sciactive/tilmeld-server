<?php namespace Tilmeld\Exceptions;

/**
 * EmailChangeRateLimitExceededException exception.
 *
 * This exception is thrown when a user attempts to change their email address
 * before the rate limit time has passed.
 *
 * @package Tilmeld
 * @license http://www.gnu.org/licenses/lgpl.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
class EmailChangeRateLimitExceededException extends \Exception {}
