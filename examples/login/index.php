<?php

// This is how you enter the setup app.
$tilmeldURL = '../../'; // This is the URL of the Tilmeld root.
$sciactiveBaseURL = '../../node_modules/'; // This is the URL of the SciActive libraries.
$restEndpoint = '../rest.php'; // This is the URL of the Nymph endpoint.

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
<html>
<head>
  <title>Login Component Example</title>
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
  <script src="<?php echo htmlspecialchars($tilmeldURL); ?>lib/Components/TilmeldLogin.js"></script>

  <link rel="stylesheet" href="<?php echo htmlspecialchars($sciactiveBaseURL); ?>pform/css/pform.css">
</head>
<body>
  <div>
    Currently logged in user: <button onclick="logout()">Logout</button> <pre class="currentuser"></pre>
  </div>
  <div class="login-container">
    <div class="login">
      <h2>Login (Normal Layout)</h2>
      <hr />
      <login data-layout="normal"></login>
      <hr />
      <div>
        Register event: <pre class="registerevent"></pre>
      </div>
      <div>
        Login event: <pre class="loginevent"></pre>
      </div>
    </div>
    <div class="login">
      <h2>Login (Small Layout)</h2>
      <hr />
      <login data-layout="small"></login>
      <hr />
      <div>
        Register event: <pre class="registerevent"></pre>
      </div>
      <div>
        Login event: <pre class="loginevent"></pre>
      </div>
    </div>
    <div class="login">
      <h2>Login (Compact Layout)</h2>
      <hr />
      <login data-layout="compact"></login>
      <hr />
      <div>
        Register event: <pre class="registerevent"></pre>
      </div>
      <div>
        Login event: <pre class="loginevent"></pre>
      </div>
    </div>
  </div>

  <script>
    let currentUser = null;
    const currentUserEl = document.querySelector('.currentuser');
    const logins = document.getElementsByTagName('login');
    const User = window.User.default;

    for (const login of logins) {
      const component = new TilmeldLogin({
        target: login,
        data: {
          autofocus: false,
          existingUser: true,
          layout: login.dataset.layout
        }
      });

      component.on('register', e => {
        const el = login.parentNode.querySelector('.registerevent');
        el.innerText = 'Fired: - '+JSON.stringify(e);
      });

      component.on('login', e => {
        const el = login.parentNode.querySelector('.loginevent');
        el.innerText = 'Fired: - '+JSON.stringify(e);
      });
    }

    User.current().then(user => {
      if (user) {
        currentUser = user;
        currentUserEl.innerText = JSON.stringify(user);
      } else {
        currentUserEl.innerText = 'none';
      }
    });

    User.on('login', user => {
      currentUser = user;
      currentUserEl.innerText = JSON.stringify(user);
    });
    User.on('logout', () => {
      currentUser = null;
      currentUserEl.innerText = 'none';
    });

    function logout() {
      if (currentUser) {
        currentUser.logout();
      }
    }
  </script>

  <style>
    .login-container {
      display: flex;
      align-items: flex-start;
      justify-content: space-around;
    }

    .currentuser, .registerevent, .loginevent {
      max-width: 200px;
      overflow: auto;
    }
  </style>
</body>
</html>
