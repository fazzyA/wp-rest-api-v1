<?php
/**
* Plugin Name: Rest Api Routes
* Plugin URI: http://www.web-axis.com/
* Description: This is rest api routes register plugin.
* Version: 1.0
* Author: Faiza Aziz Khan
* Author URI: http://www.web-axis.com/
**/

// first hook an action
add_action('rest_api_init', 'wp_rest_endpoints');

function wp_rest_endpoints($request) {
  /**
   * Handle Register User request.
   */
  register_rest_route('wp/v1', 'users/register', array(
    'methods' => 'POST',
    'callback' => 'wc_rest_user_register_endpoint_handler',
  ));
}

//////////////functions////////////////////////////////////////////////
function wc_rest_user_register_endpoint_handler($request = null) {
  $response = array();
  $parameters = $request->get_json_params();
  $username = sanitize_text_field($parameters['username']);
  $email = sanitize_text_field($parameters['email']);
  $password = sanitize_text_field($parameters['password']);
  // $role = sanitize_text_field($parameters['role']);
  $error = new WP_Error();
  if (empty($username)) {
    $error->add(400, __("Username field 'username' is required.", 'wp-rest-user'), array('status' => 400));
    return $error;
  }
  if (empty($email)) {
    $error->add(401, __("Email field 'email' is required.", 'wp-rest-user'), array('status' => 400));
    return $error;
  }
  if (empty($password)) {
    $error->add(404, __("Password field 'password' is required.", 'wp-rest-user'), array('status' => 400));
    return $error;
  }
  // if (empty($role)) {
  //  $role = 'subscriber';
  // } else {
  //     if ($GLOBALS['wp_roles']->is_role($role)) {
  //      // Silence is gold
  //     } else {
  //    $error->add(405, __("Role field 'role' is not a valid. Check your User Roles from Dashboard.", 'wp_rest_user'), array('status' => 400));
  //    return $error;
  //     }
  // }
  $user_id = username_exists($username);
  if (!$user_id && email_exists($email) == false) {
    $user_id = wp_create_user($username, $password, $email);
    if (!is_wp_error($user_id)) {
      // Ger User Meta Data (Sensitive, Password included. DO NOT pass to front end.)
      $user = get_user_by('id', $user_id);
      // $user->set_role($role);
      $user->set_role('Candidate');
      // Ger User Data (Non-Sensitive, Pass to front end.)
      $response['code'] = 200;
      $response['message'] = __("User '" . $username . "' Registration was Successful", "wp-rest-user");
    } else {
      return $user_id;
    }
  } else {
    $error->add(406, __("Email already exists, please try 'Reset Password'", 'wp-rest-user'), array('status' => 400));
    return $error;
  }
  return new WP_REST_Response($response, 123);
}// end user register function//////////////////////////////////////////////////////////////////////////