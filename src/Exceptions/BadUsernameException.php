<?php namespace Tilmeld\Exceptions;

/**
 * BadUsernameException exception.
 *
 * This exception is thrown when a user attempts to change their username to one
 * that is already taken or doesn't meet minimum criteria.
 *
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @see http://tilmeld.org/
 */
class BadUsernameException extends \Exception {
}
