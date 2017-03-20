<?php
function browse_pager($app) {
    $categoryId = input("category_id", "all");
    $search = input("term");
    $type = input("type", "upcoming");
    $page = input("page");
    $limit = input("limit", 10);
    $events = get_events($type, $search, $limit, false, $categoryId);

    $result = array(
        'categories' => array(
            array(
                'id' => 'all',
                'title' => lang('all-categories')
            )
        ),
        'events' => array(

        )
    );

    foreach(get_event_categories() as $category) {
        $result['categories'][] = array(
            'id' => $category['id'],
            'title' => lang($category['title'])
        );
    }

    foreach($events->results() as $listing) {
        $result['events'][] = api_arrange_event($listing);
    }

    return json_encode($result);
}

function birthdays_pager($app) {
    $results = array(
        'todays' => array(),
        'thismonth' => array(),
        'months' => array()
    );

    $users = event_get_today_birthdays();
    foreach($users as $user) {
        $results['todays'][] = api_arrange_user($user);
    }

    $users = event_get_month_birthdays();
    foreach($users as $user) {
        $results['thismonth'][] = api_arrange_user($user);
    }

    foreach(event_get_user_months_birthdays() as $month => $users) {
        $thisMonth = array(
            'title' => $month,
            'users' => array()
        );
        foreach($users as $user) {
            $thisMonth['users'][] = api_arrange_user($user);
        }
        $results['months'][] = $thisMonth;
    }

    return json_encode($results);
}

function get_categories_pager($app) {
    $result = array();
    foreach(get_event_categories() as $category) {
        $result[] = array(
            'id' => $category['id'],
            'title' => lang($category['title']),
        );
    }

    return json_encode($result);
}

function create_pager($app) {
    $result = array(
        'status' => 0,
        'message' => ''
    );
    $val = array(
        'title' => input('title'),
        'description' => input('description'),
        'category' => input('category_id'),
        'location' => input('location'),
        'start_day' => input('start_day'),
        'start_month' => input('start_month'),
        'start_year' => input('start_year'),
        'start_hour' => input('start_hour'),
        'start_minute' => input('start_minute'),
        'start_time_type' => input('start_time_type'),
        'address' => input('address'),
        'privacy' => input('privacy'),
        'end_day' => input('end_day'),
        'end_month' => input('end_month'),
        'end_year' => input('end_year'),
        'end_hour' => input('end_hour'),
        'end_minute' => input('end_minute'),
        'end_time_type' => input('end_time_type'),
    );
    if ($val) {
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
            $result = array_merge($result, api_arrange_event($event));
            $result['status'] = 1;
            return json_encode($result);
        } else {
            $result['message'] = validation_first();
            return json_encode($result);
        }
    }
    return json_encode($result);
}

function edit_pager($app) {
    $eventId = input("event_id");
    $event = find_event($eventId);

    $result = array(
        'status' => 0,
        'message' => ''
    );
    $val = array(
        'title' => input('title'),
        'category' => input('category_id'),
        'location' => input('location'),
        'start_day' => input('start_day'),
        'start_month' => input('start_month'),
        'start_year' => input('start_year'),
        'start_hour' => input('start_hour'),
        'start_minute' => input('start_minute'),
        'start_time_type' => input('start_time_type'),
        'address' => input('address'),
        'privacy' => input('privacy'),
        'end_day' => input('end_day'),
        'end_month' => input('end_month'),
        'end_year' => input('end_year'),
        'end_hour' => input('end_hour'),
        'end_minute' => input('end_minute'),
        'end_time_type' => input('end_time_type'),
    );
    if ($val) {
        save_event($val, $event['event_id']);
        $event = find_event($eventId);
        $result = array_merge($result, api_arrange_event($event));
        $result['status'] = 1;
        return json_encode($result);
    }
    return json_encode($result);
}

function delete_pager($app) {
    $eventId = input("event_id");
    $event = find_event($eventId);
    $result = array(
        'status' => 0
    );
    if (!is_event_admin($event)) return json_encode($result);

    delete_event($event);
    $result['status'] = 1;
    return json_encode($result);
 }

function cover_pager($app) {
    $result = array(
        'status' => 0,
        'message' => lang('general-image-error'),
        'image' => ''
    );
    $eventId = input('event_id');
    $event = find_event($eventId);
    if (!$event) return json_encode($result);
    if (!is_event_admin($event)) return json_encode($result);

    if (input_file('image')) {
        $uploader = new Uploader(input_file('image'), 'image');
        $uploader->setPath('event/'.$event['event_id'].'/'.date('Y').'/photos/cover/');
        if ($uploader->passed()) {
            $original = $uploader->resize($uploader->getWidth(), null, 'fill', 'any')->result();


            //delete the old resized cover
            if ($event['event_cover_resized']) {
                delete_file(path($event['event_cover_resized']));
            }

            //lets now crop this image for the resized cover
            $uploader->setPath('event/'.$event['event_id'].'/'.date('Y').'/photos/cover/resized/');
            $cover = $uploader->crop(0,  0, $uploader->getWidth(), ($uploader->getWidth() * 0.4))->result();
            $result['data_one'] = url_img($cover);
            $result['original'] = url_img($original);
            $result['id'] = $uploader->insertedId;
            update_event_details(array('event_cover' => $original, 'event_cover_resized' => $cover), $event['event_id']);
            $result['status'] = 1;
        } else {
            $result['message'] = $uploader->getError();
        }
    }

    return json_encode($result);
}

function rsvp_pager($app) {
    $eventId = input('event_id');
    $rsvp = input('rsvp');
    event_rsvp($eventId, $rsvp);

    return json_encode(array(
        'data_one' => count_event_going($eventId),
        'data_two' => count_event_maybe($eventId),
        'data_three' => count_event_invited($eventId)
    ));
}