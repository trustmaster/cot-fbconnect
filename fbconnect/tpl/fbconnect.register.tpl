<!-- BEGIN: MAIN -->
<div class="col3-2 first">
	<div class="block">
		<h2>{PHP.L.fbconnect_registration}</h2>
		<fb:registration
		  fields="name,birthday,gender,location,email,password,captcha"
		  redirect-uri="{FB_REGISTER_URL}"
		  width="720">
		</fb:registration>
	</div>
</div>

<div class="col3-1 first">
	<div class="block">
		<h2>{PHP.L.fbconnect_registration_login}</h2>
		<form action="{PHP|cot_url('login', 'a=check')}" method="post">
			<table class="list">
				<tr>
					<td class="width30">{PHP.L.users_nameormail}:</td>
					<td class="width70">
						<input type="text" name="rusername" maxlength="32" />
					</td>
				</tr>
				<tr>
					<td>{PHP.L.Password}:</td>
					<td>
						<input type="password" name="rpassword" maxlength="32" />
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<p class="small">
							<input type="checkbox" name="rremember" />&nbsp; {PHP.L.users_rememberme}
						</p>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="valid">
						<button type="submit" name="rlogin" value="0">{PHP.L.Login}</button>
					</td>
				</tr>
			</table>
			<a href="{PHP|cot_url('users', 'm=passrecover')}" id="passrecover" title="{PHP.L.users_lostpass}">{PHP.L.users_lostpass}</a>
		</form>
	</div>
</div>
<!-- END: MAIN -->