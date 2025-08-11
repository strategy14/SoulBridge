<?php
return $routes = [
    'GET' => [
        '/' => 'PagesController@index',
        '/home' => 'PagesController@home',
        '/logout' => 'PagesController@logout',
        '/profile' => 'PagesController@profile',
        '/message' => 'PagesController@message',
        '/search' => 'PagesController@search',
        '/like' => 'PagesController@likePost',
        '/notification' => 'PagesController@notification',
        '/error' => 'PagesController@error'
        
    ],
    'POST' => [
        '/login' => 'PagesController@login',
        '/signup' => 'PagesController@signup',
        '/story-upload' => 'PagesController@storyUpload',
        '/post-create' => 'PagesController@postHandler',
        '/like' => 'PagesController@likePost',
        '/comment' => 'PagesController@commentHandler',
        '/sendMessage' =>'PagesController@sendMessage',
        '/friendRequest' => 'PagesController@friendRequest',
        '/story-upload' => 'PagesController@storyUpload'
    ]
];