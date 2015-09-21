<?php namespace Tilmeld\Exceptions;

/**
 * BadEmailException exception.
 *
 * This exception is thrown when a user attempts to change their email address
 * to one that is already taken or doesn't meet minimum criteria.
 *
 * @package Tilmeld
 * @license http://www.gnu.org/licenses/lgpl.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
class BadEmailException extends \Exception {}
