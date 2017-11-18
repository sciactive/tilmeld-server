<?php

if (isset($_REQUEST['action']) && \Tilmeld\Tilmeld::$config['verify_email']) {
  // Verify user email addresses.
  $printPage = function ($notice) {
    echo "<!DOCTYPE html>\n";
    echo '<html>';
    echo '<head>';
    echo '<title>Email Verification</title>';
    echo '<meta charset="utf-8">';
    echo '<meta name="viewport" content="width=device-width, initial-scale=1">';
    echo '<meta http-equiv="refresh" content="4; url='.htmlspecialchars(\Tilmeld\Tilmeld::$config['verify_redirect']).'">';
    echo '<style> body {padding: 2em; font-family: Arial, sans-serif; } </style>';
    echo '</head>';
    echo '<body>';
    echo htmlspecialchars($notice);
    echo '<br />';
    echo 'You will now be redirected.';
    echo '</body>';
    echo '</html>';
  };

  $user = \Tilmeld\Entities\User::factory((int) $_REQUEST['id']);

  if (!isset($user->guid)) {
    $printPage('An error occurred.');
    return;
  }

  switch ($_REQUEST['action']) {
    case 'verifyemail':
    default:
      // Verify new user's email address.
      if (!isset($user->secret) || $_REQUEST['secret'] !== $user->secret) {
        $printPage('The secret code given does not match this user.');
        return;
      }

      if (\Tilmeld\Tilmeld::$config['unverified_access']) {
        $user->groups = (array) \Nymph\Nymph::getEntities(
            ['class' => '\Tilmeld\Entities\Group', 'skip_ac' => true],
            ['&',
              'data' => ['defaultSecondary', true]
            ]
        );
      }
      $user->enabled = true;
      unset($user->secret);
      break;
    case 'verifyemailchange':
      // Email address change.
      if (!isset($user->newEmailSecret) || $_REQUEST['secret'] !== $user->newEmailSecret) {
        $printPage('The secret code given does not match this user.');
        return;
      }

      $user->email = $user->newEmailAddress;

      if (\Tilmeld\Tilmeld::$config['email_usernames']) {
        $unCheck = $user->checkUsername();
        if (!$unCheck['result']) {
          $printPage($unCheck['message']);
          return;
        }
      }

      $test = \Nymph\Nymph::getEntity(
          ['class' => '\Tilmeld\Entities\User', 'skip_ac' => true],
          ['&',
            'ilike' => ['email', str_replace(['\\', '%', '_'], ['\\\\\\\\', '\%', '\_'], $user->newEmailAddress)],
            '!guid' => $user->guid
          ]
      );
      if (isset($test)) {
        $printPage('There is already a user with that email address. Please use a different email.');
        return;
      }

      unset($user->newEmailAddress, $user->newEmailSecret);
      break;
    case 'cancelemailchange':
      // Cancel an email address change.
      if (!isset($user->cancelEmailSecret) || $_REQUEST['secret'] !== $user->cancelEmailSecret) {
        $printPage('The secret code given does not match this user.');
        return;
      }

      $user->email = $user->cancelEmailAddress;
      unset($user->newEmailAddress, $user->newEmailSecret, $user->cancelEmailAddress, $user->cancelEmailSecret);
      break;
  }

  if ($user->saveSkipAC()) {
    switch ($_REQUEST['action']) {
      case 'verifyemail':
      default:
        $printPage('Your account has been verified.');
        break;
      case 'verifyemailchange':
        $printPage('Your new email address has been verified.');
        break;
      case 'cancelemailchange':
        $printPage('The email address change has been canceled.');
        break;
    }
  } else {
    $printPage('An error occurred.');
  }

  return;
}

if (!\Tilmeld\Tilmeld::gatekeeper('tilmeld/admin')) {
  header('HTTP/1.1 403 Forbidden');
  die('Forbidden');
}

function is_secure() {
  // Always assume secure on production.
  if (getenv('NYMPH_PRODUCTION')) {
    return true;
  }
  if (isset($_SERVER['HTTPS'])) {
    return (strtolower($_SERVER['HTTPS']) == 'on' || $_SERVER['HTTPS'] == '1');
  }
  return (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == '443');
}

?><!DOCTYPE html>
<html ng-app="setupApp">
  <head>
    <title>Tilmeld Setup App</title>
    <meta charset="utf-8">
    <script type="text/javascript">
      (function(){
        var s = document.createElement("script"); s.setAttribute("src", "https://www.promisejs.org/polyfills/promise-5.0.0.min.js");
        (typeof Promise !== "undefined" && typeof Promise.all === "function") || document.getElementsByTagName('head')[0].appendChild(s);
      })();
      NymphOptions = {
        restURL: <?php echo json_encode($restEndpoint); ?>,
        pubsubURL: '<?php echo is_secure() ? 'wss' : 'ws'; ?>://<?php echo getenv('NYMPH_PRODUCTION') ? 'nymph-pubsub-demo.herokuapp.com' : '\'+window.location.hostname+\''; ?>:<?php echo getenv('NYMPH_PRODUCTION') ? (is_secure() ? '443' : '80') : '8081'; ?>',
        rateLimit: 100
      };
      TilmeldOptions = {
        tilmeldURL: <?php echo json_encode($tilmeldURL); ?>
      };
    </script>
    <script src="<?php echo htmlspecialchars($sciactiveBaseURL); ?>nymph-client/lib/Nymph.js"></script>
    <script src="<?php echo htmlspecialchars($sciactiveBaseURL); ?>nymph-client/lib/Entity.js"></script>
    <script src="<?php echo htmlspecialchars($sciactiveBaseURL); ?>nymph-client/lib/PubSub.js"></script>
    <script src="<?php echo htmlspecialchars($tilmeldURL); ?>lib/Entities/User.js"></script>
    <script src="<?php echo htmlspecialchars($tilmeldURL); ?>lib/Entities/Group.js"></script>

    <script src="//ajax.googleapis.com/ajax/libs/angularjs/1.3.15/angular.min.js"></script>
    <script src="//ajax.googleapis.com/ajax/libs/angularjs/1.3.15/angular-route.js"></script>

    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
    <link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>

    <link rel="stylesheet" href="<?php echo htmlspecialchars($sciactiveBaseURL); ?>pform/css/pform.css">
    <link rel="stylesheet" href="<?php echo htmlspecialchars($sciactiveBaseURL); ?>pform/css/pform-bootstrap.css">

    <script src="<?php echo htmlspecialchars($tilmeldURL); ?>setup/setupApp.js"></script>
  </head>
  <body>
    <div class="container" ng-controller="MainController">
      <div class="page-header">
        <h1>Tilmeld Setup App</h1>
      </div>
      <div class="row">
        <div class="col-lg-3">
          <ul class="nav nav-pills nav-stacked">
            <li role="presentation" ng-class="{active: $location.path() === '/'}"><a href="#/">Instructions</a></li>
            <li role="presentation" ng-class="{active: $location.path().indexOf('/user/') === 0}"><a href="#/user/">Users</a></li>
            <li role="presentation" ng-class="{active: $location.path().indexOf('/group/') === 0}"><a href="#/group/">Groups</a></li>
          </ul>
        </div>
        <div class="col-lg-9">
          <div ng-view></div>
        </div>
      </div>
    </div>
  </body>
</html>
