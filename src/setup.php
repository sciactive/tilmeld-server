<?php

//if (!\Tilmeld\User::current(true)->gatekeeper('tilmeld/admin')) {
//	header('HTTP/1.1 403 Forbidden');
//	die('You are not authorized to access this page.');
//}

$timezones = \DateTimeZone::listIdentifiers();
sort($timezones);

?>
<!DOCTYPE html>
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
				pubsubURL: 'ws://<?php echo getenv('DATABASE_URL') ? htmlspecialchars('nymph-pubsub-demo.herokuapp.com') : htmlspecialchars($_SERVER['HTTP_HOST']); ?>:<?php echo getenv('DATABASE_URL') ? '80' : '8080'; ?>',
				rateLimit: 100
			};
			TilmeldOptions = {
				tilmeldURL: <?php echo json_encode($tilmeldURL); ?>,
				timezones: <?php echo json_encode($timezones); ?>,
				emailUsernames: <?php echo json_encode(\Tilmeld\Tilmeld::$config['email_usernames']); ?>
			};
		</script>
		<script src="<?php echo htmlspecialchars($sciactiveBaseURL); ?>nymph-client/src/Nymph.js"></script>
		<script src="<?php echo htmlspecialchars($sciactiveBaseURL); ?>nymph-client/src/Entity.js"></script>
		<script src="<?php echo htmlspecialchars($sciactiveBaseURL); ?>nymph-client/src/NymphPubSub.js"></script>
		<script src="<?php echo htmlspecialchars($tilmeldURL); ?>src/User.js"></script>
		<script src="<?php echo htmlspecialchars($tilmeldURL); ?>src/Group.js"></script>

		<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.3.15/angular.min.js"></script>
		<script src="//ajax.googleapis.com/ajax/libs/angularjs/1.3.15/angular-route.js"></script>

		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
		<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
		<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>

		<link rel="stylesheet" href="<?php echo htmlspecialchars($sciactiveBaseURL); ?>pform/css/pform.min.css">
		<link rel="stylesheet" href="<?php echo htmlspecialchars($sciactiveBaseURL); ?>pform/css/pform-bootstrap.min.css">

		<script src="<?php echo htmlspecialchars($tilmeldURL); ?>src/setupApp.js"></script>

		<style type="text/css">
			form {
				padding-bottom: 64px;
			}
			.button-panel {
				position: fixed;
				bottom: 0;
				padding-bottom: 0;
				margin-bottom: 0;
				border-radius: 0;
				width: 100%;
				border: 0;
				z-index: 100;
				height: 64px;
			}
			.tab-content {
				padding-top: 20px;
			}
		</style>
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
