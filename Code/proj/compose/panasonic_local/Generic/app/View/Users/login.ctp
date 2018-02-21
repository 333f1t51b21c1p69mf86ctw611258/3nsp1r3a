<?php $this->layout = 'login'; ?>

<link href="/Generic/css/signin.css" rel="stylesheet">

<div class="container" id="login" style="margin-top: 20%;">
<form class="form-signin" role="form" action="/Generic/Users/login" id="UserLoginForm" method="post" accept-charset="utf-8">
    <div style="display:none;">
        <input type="hidden" name="_method" value="POST" class="form-control">
    </div>
        <div class="input text">
            <input name="data[User][username]" maxlength="255" type="text" id="UserUsername" placeholder="username" class="form-control" required autofocus>
        </div>
        <div class="input password">
            <input name="data[User][password]" type="password" id="UserPassword" placeholder="password" class="form-control" required>
        </div>

    <div class="submit" style="display:inline;">
        <button class="btn btn-lg btn-primary btn-block" style="margin:0; padding:0;" type="submit" value="Login">Login</button>
    </div>
    <?php
    echo $this->Session->flash(); ?>

</form>
</div>

