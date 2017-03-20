<?php
return array(
    'title' => 'Video',
    'description' => "Control video plugin settings here",
    'settings' => array(
        'video-upload' => array(
            'type' => 'boolean',
            'title' => 'Enable Video Upload',
            'description' => 'With this option you can disable or enable video upload',
            'value' => 0
        ),
        'video-encoder' => array(
            'type' => 'selection',
            'title' => 'Video Encoder',
            'description' => 'Set your preferred video encoder, if none is selected only mp4 is allowed and will not be encoded',
            'value' => 'none',
            'data' => array(
                'none' => 'None',
                'ffmpeg' => 'FFmpeg',
            ),
        ),
        'default-video-list-type' => array(
            'type' => 'selection',
            'title' => 'Default Video List Type',
            'description' => 'With this option you can set the video list type',
            'value' => 'list',
            'data' => array(
                'list' => 'List',
                'grid' => 'Grid'
            )
        ),

        'video-list-limit' => array(
            'type' => 'text',
            'title' => 'Video Listing Per Page',
            'description' => 'Set your limit per page listing of videos',
            'value' => '10'
        ),

        'default-video-privacy' => array(
            'type' => 'selection',
            'title' => 'Default Video Privacy',
            'description' => 'Set the default video privacy for your members when adding new videos',
            'value' => 1,
            'data' => array(
                '1' => lang('public'),
                '2' => lang('user-connections')
            )
        ),

        'video-ffmpeg-path' => array(
            'type' => 'text',
            'title' => 'FFMpeg Path',
            'description' => 'Set your FFmpeg extension full path <strong>For example : C:\ffmpeg.exe</strong>',
            'value' => '/ffmpeg/bin/ffmpeg.exe'
        ),


    )
);
 