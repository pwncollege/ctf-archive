<?php
/**
 * Plugin Name: My Plugin
 * Plugin URI:  http://localhost/wordpress
 * Description: No description provided.
 * Version:     1.0.0
 * Author:      duck
 * Author URI:  https://github.com/anaverageduck
 * Text Domain: my-plugin
 *
 */

require_once ABSPATH . '/vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

add_filter( 'wp_password_change_notification_email', '__return_false' );

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_script(
        'my-plugin-script',
        plugin_dir_url(__FILE__) . 'app.js',
        [],
        null,
        true
    );

    wp_localize_script('my-plugin-script', 'MyPlugin', [
        'restUrl' => rest_url(),
        'nonce'   => wp_create_nonce('wp_rest'),
    ]);
});

function validate_token($token) {
    $secret = JWT_AUTH_SECRET_KEY;
    $admin_secret = JWT_AUTH_ADMIN_SECRET_KEY;

    list($headersB64, $payloadB64, $sig) = explode('.', $token);
    $decoded = json_decode(base64_decode($payloadB64), true);

    $id = $decoded['id'] ?? null;
    error_log("Decoded token for user ID: $id");
    if (!$id) {
        return false;
    }

    $user = get_user_by('id', $id);
    if (!$user) {
        return false;
    }

    try {
        if (in_array('administrator', $user->roles)) {
            $decoded = JWT::decode($token, new Key($admin_secret, 'HS256'));
        } else {
            $decoded = JWT::decode($token, new Key($secret, 'HS256'));
        }
    } catch (Exception $e) {
        return false;
    }

    return $decoded;
}

add_action('rest_api_init', function () {
    register_rest_route('legacy/v1', '/get-token', [
        'methods' => 'GET',
        'callback' => function (WP_REST_Request $request) {
            $user = wp_get_current_user();
            if (!$user->exists()) {
                return new WP_REST_Response(['error' => 'not_logged_in'], 401);
            }

            $payload = [
                'id' => $user->ID,
                'email' => $user->user_email,
            ];

            $jwt_token = null;
            if (in_array('administrator', $user->roles)) {
                $jwt_token = JWT::encode($payload, JWT_AUTH_ADMIN_SECRET_KEY, 'HS256');
            } else {
                $jwt_token = JWT::encode($payload, JWT_AUTH_SECRET_KEY, 'HS256');
            }
            return new WP_REST_Response(['token' => $jwt_token], 200);
        },
        'permission_callback' => function () {
            return is_user_logged_in();
        }
    ]);

    register_rest_route('legacy/v1', '/change-email', [
        'methods' => 'POST',
        'callback' => function (WP_REST_Request $request) {
            $email = $request->get_param('email');
            $jwt_token = $request->get_param('token');

            if (!$email) {
                return new WP_REST_Response(['error' => 'missing_email'], 400);
            }

            if (!$jwt_token) {
                return new WP_REST_Response(['error' => 'missing_token'], 400);
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                return new WP_REST_Response(['error' => 'invalid_email'], 400);
            }

            $payloadB64 = explode('.', $jwt_token)[1] ?? '';
            $claimant_id = json_decode(base64_decode($payloadB64), true)['id'] ?? null;

            if (!$claimant_id) {
                return new WP_REST_Response(['error' => 'malformed_token'], 400);
            }

            $base_data = [
                'ID' => $claimant_id,
                'user_email' => $email
            ];
            $update_data = array_merge($base_data, $request->get_params());

            $target_id = $update_data['ID'];

            $target_user = get_user_by('id', $target_id);
            if (!$target_user) {
                return new WP_REST_Response(['error' => 'no_user'], 404);
            }

            try {
                if (in_array('administrator', $target_user->roles)) {
                    $decoded = JWT::decode($jwt_token, new Key(JWT_AUTH_ADMIN_SECRET_KEY, 'HS256'));
                } else {
                    $decoded = JWT::decode($jwt_token, new Key(JWT_AUTH_SECRET_KEY, 'HS256'));
                }
            } catch (Exception $e) {
                return new WP_REST_Response(['error' => 'invalid_signature_for_tier'], 401);
            }

            $res = wp_update_user($update_data);
            if (is_wp_error($res)) {
                return new WP_REST_Response(['error' => 'update_failed', 'details' => $res->get_error_message()], 500);
            }

            return new WP_REST_Response(['status' => 'updated'], 200);
        },
        'permission_callback' => '__return_true'
    ]);

    register_rest_route('legacy/v1', '/update-avatar', [
        'methods' => 'POST',
        'callback' => function (WP_REST_Request $request) {
            $jwt_token = $request->get_param('token');
            if (!$jwt_token) {
                return new WP_REST_Response(['error' => 'missing_token'], 400);
            }

            $decoded = validate_token($jwt_token);
            if (!$decoded) {
                return new WP_REST_Response(['error' => 'invalid_token'], 401);
            }

            $files = $request->get_file_params();

            if (empty($files['avatar'])) {
                return new WP_REST_Response(['error' => 'missing_avatar'], 400);
            }

            $file = $files['avatar'];
            $filename = basename($file['name']);
            $ext = pathinfo($filename, PATHINFO_EXTENSION);

            $allowed_exts = ['.jpg', '.jpeg', '.png', '.gif'];
            $is_allowed = false;

            foreach ($allowed_exts as $allowed) {
                if (strpos(strtolower($filename), $allowed) !== false) {
                    $is_allowed = true;
                    break;
                }
            }

            if (!$is_allowed) {
                return new WP_REST_Response(['error' => 'mime_type_not_synergistic'], 403);
            }

            $upload_dir = wp_upload_dir();
            $target_path = $upload_dir['path'] . '/' . $filename;

            if (move_uploaded_file($file['tmp_name'], $target_path)) {

                $token_user_id = is_object($decoded) ? $decoded->id : $decoded['id'];

                $target_user_id = $request->get_param('ID') ?? $token_user_id;

                update_user_meta($target_user_id, 'legacy_custom_avatar', $upload_dir['url'] . '/' . $filename);

                return new WP_REST_Response([
                    'status' => 'avatar_hydrated',
                    'file_url' => $upload_dir['url'] . '/' . $filename
                ], 200);
            }

            return new WP_REST_Response(['error' => 'ingestion_failed'], 500);
        },
        'permission_callback' => '__return_true'
    ]);
});

add_action('woocommerce_after_my_account', function () {
    ?>
    <div id="legacy-avatar-debug-container" hidden>
        <h3>Legacy Avatar Upload (Staging)</h3>
        <form id="avatarForm" action="/wp-json/legacy/v1/update-avatar" method="POST" enctype="multipart/form-data">
            <input type="text" name="token" id="jwt_token" placeholder="Paste test JWT here">
            <input type="file" name="avatar" accept=".jpg,.jpeg,.png,.gif">
            <button type="submit">Upload</button>
        </form>
    </div>
    <?php
});
