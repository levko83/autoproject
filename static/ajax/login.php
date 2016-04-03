<div id="login_mw" class="modal_window">

	<button class="close arcticmodal-close"></button>

	<header class="on_the_sides">
		<div class="left_side">

			<h2>Log In</h2>

		</div>

		<div class="right_side">

			<a href="/account/signup/" class="button_grey middle_btn">Register</a>

		</div>

	</header>

	<form class="type_2" action="/account/login/" method="POST">

		<ul>

			<li>
				<label for="login_email">Email address</label>
				<input type="email" name="form[email]" id="login_email" required>
			</li>

			<li>
				<label for="login_password">Password</label>
				<input type="password" name="form[pass]" id="login_password" required>
			</li>

			<li>
				<input type="checkbox" name="form[rememberme]" id="checkbox_1" value="1">
				<label for="checkbox_1">Remember me</label>
			</li>

			<li class="v_centered">
				<button class="button_blue middle_btn">Login</button>
				<a href="/account/remide/" class="small_link">Forgot your password?</a>
			</li>

		</ul>

	</form>

</div>