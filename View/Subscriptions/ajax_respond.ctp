<?php

$success = (isset($success)?$success:false);
$message = (isset($message)?$message:false);
$subscribed = (isset($subscribed)?$subscribed:false);
$user_id = (isset($user_id)?$user_id:false);
$uri = (isset($uri)?$uri:false);
$redirect = (isset($redirect)?$redirect:false);

echo json_encode([
	'success' => $success,
	'message' => $message,
	'subscribed' => $subscribed,
	'user_id' => $user_id,
	'uri' => $uri,
	'redirect' => $redirect,
]);