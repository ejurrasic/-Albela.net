<?php
function push_pager($app) {
    $app->setTitle(lang('Push Notification'));

    $val = input('val', null, array('body'));
    $message = null;
    if ($val) {
        CSRFProtection::validate();
        /**
         * @var $subject
         * @var $body
         * @var $to
         * @var $non
         * @var $selected
         */
        extract($val);
        $body = lawedContent(stripslashes($body));
        $db = null;
        if ($to == 'all') {
            $db = db()->query("SELECT * FROM users  WHERE gcm_token !=''");
        } elseif ($to == 'selected') {
            if (isset($selected)) {
                $selected = implode(',', $selected);
                $db = db()->query("SELECT * FROM users  WHERE id IN ({$selected})  AND gcm_token !='' ");
            }
        } elseif ($to == 'non-active') {
            $number = (int) $non['number'];
            $type = $non['type'];
            $time = time();

            if ($type == 'day') {
                $time = $time - ($number * 86400);
            } elseif($type == 'month') {
                $time = $time - ($number * 2628000);
            } else {
                $time = $time - ($number * 31540000);
            }

            $db = db()->query("SELECT * FROM users  WHERE online_time < {$time} AND gcm_token !=''");
        }

        if ($db) {
            while($user = $db->fetch_assoc()) {
                $message = json_encode(array(
                    'type' => 'push-notification',
                    'message' => $body
                ));
                //exit($user['gcm_token']);
                $msg = array
                (
                    'message' 	=> $message
                );

                //Creating a new array fileds and adding the msg array and registration token array here
                $fields = array
                (
                    'registration_ids' 	=> array($user['gcm_token']),
                    'data'			=> $msg
                );

                $headers = array
                (
                    'Authorization: key=' .config("google-fcm-api-key"),
                    'Content-Type: application/json'
                );

                //Using curl to perform http request
                $ch = curl_init();
                curl_setopt( $ch,CURLOPT_URL, 'https://fcm.googleapis.com/fcm/send' );
                curl_setopt( $ch,CURLOPT_POST, true );
                curl_setopt( $ch,CURLOPT_HTTPHEADER, $headers );
                curl_setopt( $ch,CURLOPT_RETURNTRANSFER, true );
                curl_setopt( $ch,CURLOPT_SSL_VERIFYPEER, false );
                curl_setopt( $ch,CURLOPT_POSTFIELDS, json_encode( $fields ) );

                //Getting the result
                $result = curl_exec($ch );
                curl_close( $ch );

                $res = json_decode($result);
                //exit($result);
            }
            $message = lang('Push Notification sent successfully');
        } else {
            $message = lang('Push notification was not successful');
        }
    }


    return $app->render(view('api::push', array('message' => $message)));
}