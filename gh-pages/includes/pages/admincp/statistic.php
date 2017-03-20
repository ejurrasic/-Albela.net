<?php
function statistic_page($app) {
    get_menu('admin-menu', 'admin-statistic')->setActive();
    set_title(lang('admin-dashboard'));
    $statistics = array(
        'members' => array(
            'count' => count_table_rows('users'),
            'title' => lang('members'),
            'icon' => 'ion-ios-people-outline',
            'link' => url_to_pager('admin-members-list'),
        ),

        'online-members' => array(
            'count' => count_online_members(),
            'title' => lang('online-members'),
            'icon' => 'ion-ios-people-outline',
            'link' => url_to_pager('admin-members-list').'?filter=online',
        ),


        'verification-requests' => array(
            'count' => count_verification_requests(),
            'title' => lang('verification-requests'),
            'icon' => 'ion-checkmark-circled',
            'link' => url_to_pager('admin-requests'),
        ),

    );
    $statistics = fire_hook('admin.statistics', $statistics);
    return $app->render(view('dashboard/statistics', array('statistics' => $statistics)));
}

function load_pager($app){
    CSRFProtection::validate(false);
    $type = input('type', 'chart');
    $result = array(
        'server' => '',
        'charts' => array()
    );

    switch($type) {
        case 'server':
            $result['server'] = view('dashboard/server');
            break;
        case 'chart':
            $year = input('year', date('Y'));

            $months = array('Jan' => 1,'Feb' => 2,'Mar' => 3,'April' => 4,'May' => 5,'Jun' => 6,'Jul' => 7,'Aug' => 8,'Sept' => 9,'Oct' => 10,'Nov' => 11,'Dec' => 12);
            $c = array(
                'name' => lang('members'),
                'points' => array()
            );


            foreach($months as $name => $n) {
                $c['points'][$name] = count_users_in_month($n, $year);
            }

            $result['charts']['members'] = array($c);

            $result = fire_hook('admin.charts', $result, array($months, $year));
            break;
    }

    return json_encode($result);
}