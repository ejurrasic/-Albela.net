<?php
require path('plugins'.DIRECTORY_SEPARATOR.'mediachat'.DIRECTORY_SEPARATOR.'library'.DIRECTORY_SEPARATOR.'Twilio'.DIRECTORY_SEPARATOR.'autoload.php'); // Loads the library
use Twilio\Jwt\AccessToken;
use Twilio\Jwt\Grants\VideoGrant;

function get_identity_pager($app) {
    $result = array('status' => 0);
    $cid = input("cid");
    $theUserid = input("theuserid");
    if (!$cid) {
        //try find there cid
        $theirCid = get_conversation_id(array($theUserid));
        $cid = ($theirCid) ? $theirCid : null;
    }

    if ($cid) {
        $conversation = get_conversation($cid);
        if ($conversation and $conversation['type'] == 'single') {
            if ($conversation['user1'] == get_userid()) {
                $theUserid = $conversation['user2'];
            } else {
                $theUserid = $conversation['user1'];
            }
        }

    }

    if ($theUserid) {
        $user = find_user($theUserid);
        $result['status']  = 1;
        $result['data_one'] = $theUserid;
        $result['data_two'] = get_user_name($user);
        $result['data_three']  = get_avatar(600, $user);
    }

    return json_encode($result);
}
function init_pager($app) {
    $identity = input('identity');
    $callType = input('call_type');
    $which = input('which');
    $connectionId = input('connection_id');

    $enableVideo = ($callType == 1) ? true : false;
    if ($which == 'call') {
        mediachat_init_call($identity, $connectionId, $enableVideo);
    } else {
        mediachat_receive_call($identity);
    }

    $sid = config('twilio-sid');
    $api_key = config('twilio-api-key');
    $api_secret = config('twilio-api-secret');
    $rtcp_sid = config('rtcp-sid');

    $token = new AccessToken($sid, $api_key, $api_secret, 3600, get_userid());
    $grant = new VideoGrant();
    $grant->setConfigurationProfileSid($rtcp_sid);
    $token->addGrant($grant);

    $result = array(
        'status' => 1,
        'data_one' => $token->toJWT()
    );

    return json_encode($result);
}

function get_pending_pager($app) {
    $result = array('status' => 0);
    $pending_calls = mediachat_user_pending_calls();
    if(isset($pending_calls[0])) {
        $type = $pending_calls[0]['enable_video'] == 1 ? 'video' : 'voice';
        mediachat_see_call($pending_calls[0]['id']);
        $a = array(
            'data_one' => $pending_calls[0]['caller_id'],
            'data_two' => get_user_name($pending_calls[0]['caller_id']),
            'data_three' => get_avatar(200, find_user($pending_calls[0]['caller_id'])),
            'data_four' => $type,
            'data_five' => $pending_calls[0]['connection_id'],
        );
        $result = array_merge($result, $a);
        $result['status'] = 1;
    }

    return json_encode($result);
}