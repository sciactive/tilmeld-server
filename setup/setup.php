<?php

//if (!\Tilmeld\Entities\User::current(true)->gatekeeper('tilmeld/admin')) {
//	header('HTTP/1.1 403 Forbidden');
//	die('You are not authorized to access this page.');
//}

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
    <script src="<?php echo htmlspecialchars($sciactiveBaseURL); ?>nymph-client/lib-umd/Nymph.js"></script>
    <script src="<?php echo htmlspecialchars($sciactiveBaseURL); ?>nymph-client/lib-umd/Entity.js"></script>
    <script src="<?php echo htmlspecialchars($sciactiveBaseURL); ?>nymph-client/lib-umd/PubSub.js"></script>
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
