<?php
if (is_loggedIn()) get_menu('dashboard-main-menu', 'events')->setActive();
function events_pager($app) {
    $app->setTitle(lang('event::events'));
    $type = input('type', 'upcoming');
    $app->eventType = $type;
    if ($type == 'birthdays') {
        return _render(view('event::birthdays', array('events' => get_events($type, input('term'), 10, false, input('category')))), $type);
    }
    return _render(view('event::browse', array('events' => get_events($type, input('term'), 10, false, input('category')))), $type);
}

function event_delete_pager($app) {
    $eventId = segment(2);
    $event = find_event($eventId);
    if (!is_event_admin($event)) return redirect_to_pager('events');

    delete_event($event);
    if (input('admin')) redirect_back();
    return redirect_to_pager('events');
}

function events_run_pager($app) {
    $type = input('type', 'event');
    $when = input('when', 'on');
    $day = date('j');
    $month = date('n');
    $year = date('Y');
    if ($when == 'before') {
        $day = $day + 1;
    }

    switch($type) {
        case 'event':
            //exit($day.'-'.$month.'-'.$year);
            $query = db()->query("SELECT event_id,user_id FROM events WHERE event_day='{$day}' AND event_month='{$month}' AND event_year='{$year}'");
            while($event = $query->fetch_assoc()) {
                $eventId = $event['event_id'];

                $q = db()->query("SELECT user_id FROM event_invites WHERE event_id='{$eventId}' AND (rsvp = '1' OR rsvp = '2')");
                while($user = $q->fetch_assoc()) {
                    $userid =   $user['user_id'];
                    send_notification($userid, 'event.events', $eventId, array('when' => $when),null, null, $event['user_id']);
                }
            }
            break;
        case 'birthday':
            $month = event_get_month_name($month);
            $query = db()->query("SELECT id FROM users WHERE birth_day='{$day}' AND birth_month='{$month}'");
            while($user = $query->fetch_assoc()) {
                $userid = $user['id'];
                $friends = get_friends($userid);
                foreach($friends as $friend) {
                    send_notification($friend, 'event.birthday', $when, array(),null, null,  $userid);
                }
            }
            break;
    }

    if (input('web')) return redirect_back();
}

function create_event_pager($app) {
    $message = null;
    $app->setTitle(lang('event::create-event'));
    $val = input('val');
    if ($val) {
		CSRFProtection::validate();
        $validator = validator($val, array(
            'category' => 'required',
            'title' => 'required',
            'location' => 'required',
            'start_day' => 'required',
            'start_month' => 'required',
            'start_year' > 'required',
        ));

        if (validation_passes()) {
            $eventId  = create_event($val);
            $event = find_event($eventId);
            return redirect(event_url(null, $event));
        } else {
            $message = validation_first();
        }
    }
    return _render(view('event::create', array('message' => $message)), 'create', true);
}

/**
 * Help function to render page with its layout
 */
function _render($content, $type = "all", $fullWidth = false) {

    return app()->render(view("event::layout", array('content' => $content, 'type' => $type, 'fullWidth' => $fullWidth)));
}



 