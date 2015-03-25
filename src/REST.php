<?php namespace Tilmeld;
/**
 * Simple Tilmeld REST server implementation.
 *
 * @package Tilmeld
 * @license http://www.gnu.org/licenses/lgpl.html
 * @author Hunter Perrin <hperrin@gmail.com>
 * @copyright SciActive.com
 * @link http://sciactive.com/
 */
use SciActive\RequirePHP as RequirePHP;

/**
 * REST class.
 *
 * Provides Tilmeld functionality compatible with a REST API. Allows the
 * developer to design their own API, or just use the reference implementation.
 *
 * @package Tilmeld
 */
class REST {
	/**
	 * Run the Tilmeld REST server process.
	 *
	 * Note that on failure, an HTTP error status code will be sent, usually
	 * along with a message body.
	 *
	 * @param string $method The HTTP method.
	 * @param string $action The Tilmeld action.
	 * @param string $data The JSON encoded data.
	 * @return bool True on success, false on failure.
	 */
	public function run($method, $action, $data) {
		$method = strtoupper($method);
		if (is_callable([$this, $method])) {
			return $this->$method($action, $data);
		}
		return $this->httpError(405, "Method Not Allowed");
	}

	protected function DELETE($action = '', $data = '') {
	}

	protected function POST($action = '', $data = '') {
		if (!in_array($action, [
				'check_email',
				'check_phone',
				'check_username',
				'login',
				'register',
			])) {
			return $this->httpError(400, "Bad Request");
		}
		$actionMap = [
			'check_email' => 'checkEmail',
			'check_phone' => 'checkPhone',
			'check_username' => 'checkUsername',
		];
		$method = $actionMap[$action];
		ob_start();
		if (in_array($action, ['check_email', 'check_phone', 'check_username'])) {
			if (!Tilmeld::$config->$action['value']) {
				return $this->httpError(404, "Not Found");
			}

			$args = json_decode($data, true);
			$id = null;
			if (!empty($args['id'])) {
				$id = intval($args['id']);
			}

			$result = Tilmeld::$method($args['value'], $id);
			header("HTTP/1.1 200 OK", true, 200);
			echo json_encode($result);
		} elseif ($action === 'login') {
			$args = json_decode($data, true);
			$this->login($args);
		} elseif ($action === 'register') {
			$args = json_decode($data, true);
			$this->register($args);
		}
		ob_end_flush();
		return true;
	}

	protected function PUT($action = '', $data = '') {
	}

	protected function GET($action = '', $data = '') {
	}

	protected function register($data) {
		if (!Tilmeld::$config->allow_registration['value']) {
			return false;
		}
		if (empty($data['password']) && !Tilmeld::$config->pw_empty['value']) {
			$this->provideResponse(false, 'Password is a required field.');
			return false;
		}
		$un_check = Tilmeld::check_username($data['username']);
		if (!$un_check['result']) {
			echo json_encode($un_check);
			return false;
		}
		$user = User::factory();
		Tilmeld::session('write');
		$_SESSION['com_user__tmpusername'] = $data['username'];
		$_SESSION['com_user__tmppassword'] = $data['password'];
		$_SESSION['com_user__tmpreferral_code'] = $data['referral_code'];
		Tilmeld::session('close');
		if (Tilmeld::$config->one_step_registration['value']) {
			$_->action('com_user', 'registeruser');
		} else {
			$reg_module = $user->print_register();
			if ( !empty($data['url']) )
				$reg_module->url = $data['url'];
		}
	}

	protected function login($data) {
		if (empty($data['username'])) {
			Tilmeld::printLogin('content', $data['url']);
			return false;
		}


		$username = $data['username'];
		if (Tilmeld::$config->email_usernames['value'] && strpos($username, '@') === false && !empty(Tilmeld::$config->default_domain['value'])) {
			$username .= '@'.Tilmeld::$config->default_domain['value'];
		}

		if (Tilmeld::gatekeeper() && $username === $_SESSION['user']->username) {
			$this->provideResponse(true, 'You are already logged in.');
			return true;
		}
		// Check that a challenge block was created within 10 minutes.
		if (
				(Tilmeld::$config->sawasc['value'] && Tilmeld::$config->pw_method['value'] !== 'salt') &&
				(!isset($_SESSION['sawasc']['ServerCB']) || $_SESSION['sawasc']['timestamp'] < time() - 600)
			) {
			$this->provideResponse(false, 'Your login request session has expired, please try again.');
			return false;
		}
		$user = User::factory($username);
		if (!isset($user->guid)) {
			$this->provideResponse(false, 'Incorrect login/password.');
			return false;
		}
		if (Tilmeld::$config->sawasc['value'] && Tilmeld::$config->pw_method['value'] != 'salt') {
			Tilmeld::session('write');
			if (!$user->checkSawasc($data['ClientHash'], $_SESSION['sawasc']['ServerCB'], $_SESSION['sawasc']['algo'])) {
				unset($_SESSION['sawasc']);
				Tilmeld::session('close');
				$this->provideResponse(false, 'Incorrect login/password.');
				return false;
			}
			unset($_SESSION['sawasc']);
			Tilmeld::session('close');
		} else {
			if (!$user->checkPassword($data['password'])) {
				$this->provideResponse(false, 'Incorrect login/password.');
				return false;
			}
		}

		// Authentication was successful, attempt to login.
		if (!Tilmeld::login($user)) {
			$this->provideResponse(false, 'Incorrect login/password.');
			return false;
		}

		// Login was successful.
		$this->provideResponse(true);
		return true;
	}

	protected function provideResponse($success, $message = null) {
		if (!isset($message)) {
			echo json_encode((object) ['result' => $success]);
		} else {
			echo json_encode((object) ['result' => $success, 'message' => $message]);
		}
	}

	/**
	 * Return the request with an HTTP error response.
	 *
	 * @param int $errorCode The HTTP status code.
	 * @param string $message The message to place on the HTTP status header line.
	 * @return boolean Always returns false.
	 * @access protected
	 */
	protected function httpError($errorCode, $message) {
		header("HTTP/1.1 $errorCode $message", true, $errorCode);
		echo "$errorCode $message";
		return false;
	}
}
