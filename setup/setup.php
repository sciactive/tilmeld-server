<?php // phpcs:disable Generic.Files.LineLength.TooLong,PSR1.Files.SideEffects.FoundWithSymbols

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
        $printPage('An error occurred.');
        return;
      }

      if (\Tilmeld\Tilmeld::$config['unverified_access']) {
        $user->groups = (array) \Nymph\Nymph::getEntities(
            ['class' => '\Tilmeld\Entities\Group', 'skip_ac' => true],
            ['&',
              'equal' => ['defaultSecondary', true]
            ]
        );
      }
      $user->enabled = true;
      unset($user->secret);
      break;
    case 'verifyemailchange':
      // Email address change.
      if (!isset($user->newEmailSecret) || $_REQUEST['secret'] !== $user->newEmailSecret) {
        $printPage('An error occurred.');
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
        $printPage('An error occurred.');
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
        restURL: <?php echo json_encode($restEndpoint); ?>
      };
    </script>
    <?php if (isset($sciactiveDevClientURL)) { ?>
      <script src="<?php echo htmlspecialchars($sciactiveDevClientURL); ?>dist/NymphClient.js"></script>
      <script src="<?php echo htmlspecialchars($sciactiveDevClientURL); ?>../tilmeld-client/dist/TilmeldClient.js"></script>
    <?php } else { ?>
      <script src="<?php echo htmlspecialchars($nodeModulesURL); ?>nymph-client/dist/NymphClient.js"></script>
      <script src="<?php echo htmlspecialchars($nodeModulesURL); ?>tilmeld-client/dist/TilmeldClient.js"></script>
    <?php } ?>

    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.9/angular.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.6.9/angular-route.js"></script>

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.slim.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.3/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta.2/js/bootstrap.min.js"></script>
  </head>
  <body>
    <div class="container" ng-controller="MainController">
      <div class="my-3 border border-top-0 border-right-0 border-left-0">
        <h1 class="display-4 mb-3">Tilmeld Setup App</h1>
      </div>
      <div class="row">
        <div class="col-md-3">
          <ul class="nav nav-pills flex-column">
            <li class="nav-item" role="presentation">
              <a href="#!/instructions" class="nav-link" ng-class="{active: $route.current.scope.name === 'InstructionsController'}">Instructions</a>
            </li>
            <li class="nav-item" role="presentation">
              <a href="#!/user" class="nav-link" ng-class="{active: $route.current.scope.name === 'UserController'}">Users</a>
            </li>
            <li class="nav-item" role="presentation">
              <a href="#!/group" class="nav-link" ng-class="{active: $route.current.scope.name === 'GroupController'}">Groups</a>
            </li>
          </ul>
        </div>
        <div class="col-md-9">
          <div ng-view></div>
        </div>
      </div>
    </div>
    <script type="text/template" id="template-instructions"><?php echo file_get_contents(__DIR__ . '/instructions.html'); ?></script>
    <script type="text/template" id="template-user"><?php echo file_get_contents(__DIR__ . '/user.html'); ?></script>
    <script type="text/template" id="template-group"><?php echo file_get_contents(__DIR__ . '/group.html'); ?></script>
    <script type="text/javascript"><?php echo file_get_contents(__DIR__ . '/setupApp.js'); ?></script>
  </body>
</html>
