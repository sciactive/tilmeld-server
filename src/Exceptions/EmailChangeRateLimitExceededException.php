<?php namespace Tilmeld\Exceptions;

/**
 * EmailChangeRateLimitExceededException exception.
 *
 * This exception is thrown when a user attempts to change their email address
 * before the rate limit time has passed.
 *
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @see http://tilmeld.org/
 */
class EmailChangeRateLimitExceededException extends \Exception {
}
