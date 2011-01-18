<!-- BEGIN: MAIN -->
<div class="mboxHD">{PHP.L.fbconnect_registration}</div>
<div class="mboxBody">
	<fb:registration
	  fields="name,birthday,gender,location,email,password,captcha"
	  redirect-uri="{FB_REGISTER_URL}"
	  width="720">
	</fb:registration>
</div>
<!-- END: MAIN -->