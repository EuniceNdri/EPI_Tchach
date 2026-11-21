<?php

$routes = [
    '' => ['controller' => null, 'method' => null],
    'landing' => ['controller' => null, 'method' => null],
    'home' => ['controller' => null, 'method' => null],
    'salon' => ['controller' => 'SalonController', 'method' => 'index'],
    'salons' => ['controller' => 'SalonController', 'method' => 'index'],
    'Salon' => ['controller' => 'SalonController', 'method' => 'index'],
    'zones' => ['controller' => 'SalonController', 'method' => 'index'],
    'zones/creer' => ['controller' => 'SalonController', 'method' => 'createZone'],
    'salon/mentionable-users' => ['controller' => 'SalonController', 'method' => 'mentionableUsers'],
    'salon/save' => ['controller' => 'SalonController', 'method' => 'toggleSaved'],
    'salon/presence' => ['controller' => 'SalonController', 'method' => 'presence'],
    'salons/create-zone' => ['controller' => 'SalonController', 'method' => 'createZone'],
    'salons/create-category' => ['controller' => 'SalonController', 'method' => 'createCategory'],
    'login' => ['controller' => 'AuthController', 'method' => 'login'],
    'register' => ['controller' => 'AuthController', 'method' => 'register'],
    'forgot-password' => ['controller' => 'AuthController', 'method' => 'forgotPassword'],
    'reset-password' => ['controller' => 'AuthController', 'method' => 'resetPassword'],
    'logout' => ['controller' => 'AuthController', 'method' => 'logout'],
    'publication/create' => ['controller' => 'PublicationController', 'method' => 'create'],
    'publication/discussion' => ['controller' => 'PublicationController', 'method' => 'discussion'],
    'publication/live-stats' => ['controller' => 'ReactionController', 'method' => 'liveStats'],
    'publication/vote' => ['controller' => 'PublicationController', 'method' => 'vote'],
    'comment/create' => ['controller' => 'PublicationController', 'method' => 'createComment'],
    'search' => ['controller' => 'SearchController', 'method' => 'search'],
    'signalement' => ['controller' => 'SignalementController', 'method' => 'create'],
    'signalement/create' => ['controller' => 'SignalementController', 'method' => 'create'],
    'moderation' => ['controller' => 'ModerationController', 'method' => 'index'],
    'moderation/action' => ['controller' => 'ModerationController', 'method' => 'decision'],
    'moderation/decision' => ['controller' => 'ModerationController', 'method' => 'decision'],
    'reaction' => ['controller' => 'ReactionController', 'method' => 'toggle'],
    'reaction/toggle' => ['controller' => 'ReactionController', 'method' => 'toggle'],
    'notifications' => ['controller' => 'NotificationController', 'method' => 'index'],
    'notifications/unread-count' => ['controller' => 'NotificationController', 'method' => 'unreadCount'],
    'notifications/mark-read' => ['controller' => 'NotificationController', 'method' => 'markAsRead'],
    'notifications/mark-all-read' => ['controller' => 'NotificationController', 'method' => 'markAllRead'],
    'notifications/delete' => ['controller' => 'NotificationController', 'method' => 'delete'],
    'profile' => ['controller' => 'ProfileController', 'method' => 'index'],
    'profile/view' => ['controller' => 'ProfileController', 'method' => 'index'],
    'profile/update' => ['controller' => 'ProfileController', 'method' => 'update'],
    'profile/edit' => ['controller' => 'ProfileController', 'method' => 'index'],
    'profil' => ['controller' => 'ProfileController', 'method' => 'index'],
    'profil/update' => ['controller' => 'ProfileController', 'method' => 'update'],
    'messages' => ['controller' => 'SocialController', 'method' => 'messages'],
    'messagerie' => ['controller' => 'SocialController', 'method' => 'messages'],
    'friends' => ['controller' => 'SocialController', 'method' => 'friendsList'],
    'profile/friends' => ['controller' => 'SocialController', 'method' => 'friendsList'],
    'amis' => ['controller' => 'SocialController', 'method' => 'friendsList'],
    'friends/action' => ['controller' => 'SocialController', 'method' => 'friendAction'],
    'binomage' => ['controller' => 'BinomageController', 'method' => 'index'],
    'binomage/action' => ['controller' => 'BinomageController', 'method' => 'action'],
    'stories/create' => ['controller' => 'StoryController', 'method' => 'create'],
    'stories/image' => ['controller' => 'StoryController', 'method' => 'image'],
    'stories/view' => ['controller' => 'StoryController', 'method' => 'markViewed'],
    'stories/viewers' => ['controller' => 'StoryController', 'method' => 'viewers'],
    'stories/delete' => ['controller' => 'StoryController', 'method' => 'delete'],
    'messages/send' => ['controller' => 'SocialController', 'method' => 'sendMessage'],
    'messages/group/send' => ['controller' => 'SocialController', 'method' => 'sendGroupMessage'],
    'messages/groups/send' => ['controller' => 'SocialController', 'method' => 'sendGroupMessage'],
    'messages/delete' => ['controller' => 'SocialController', 'method' => 'deleteMessage'],
    'dashboard' => ['controller' => 'DashboardController', 'method' => 'index'],
    'dashboard/index' => ['controller' => 'DashboardController', 'method' => 'index'],
    'dashboard/admin' => ['controller' => 'DashboardController', 'method' => 'adminDashboard'],
    'dashboard/delegue' => ['controller' => 'DashboardController', 'method' => 'delegueDashboard'],
    'dashboard/chef-classe' => ['controller' => 'DashboardController', 'method' => 'chefClasseDashboard'],
    'dashboard/chef_classe' => ['controller' => 'DashboardController', 'method' => 'chefClasseDashboard'],
    'admin' => ['controller' => 'AdminController', 'method' => 'dashboard'],
    'admin/dashboard' => ['controller' => 'AdminController', 'method' => 'dashboard'],
    'admin/users' => ['controller' => 'AdminController', 'method' => 'users'],
    'admin/users/json' => ['controller' => 'AdminController', 'method' => 'getUserJson'],
    'admin/users/edit' => ['controller' => 'AdminController', 'method' => 'editUser'],
    'admin/users/delete' => ['controller' => 'AdminController', 'method' => 'deleteUser'],
    'admin/moderation' => ['controller' => 'ModerationController', 'method' => 'index'],
    'admin/moderation/action' => ['controller' => 'ModerationController', 'method' => 'decision'],
    'admin/categories' => ['controller' => 'AdminController', 'method' => 'categories'],
    'admin/filieres' => ['controller' => 'AdminController', 'method' => 'categories'],
    'admin/zones' => ['controller' => 'AdminController', 'method' => 'categories'],
    'admin/message-groups' => ['controller' => 'AdminController', 'method' => 'messageGroups'],
    'admin/message-groups/update' => ['controller' => 'AdminController', 'method' => 'updateMessageGroups'],
    'admin/categories/create-zone' => ['controller' => 'AdminController', 'method' => 'createZone'],
    'admin/categories/create-category' => ['controller' => 'AdminController', 'method' => 'createCategory'],
    'admin/tags/action' => ['controller' => 'AdminController', 'method' => 'tagAction'],
    'admin/settings' => ['controller' => 'AdminController', 'method' => 'settings'],
    'admin/school-years' => ['controller' => 'AdminController', 'method' => 'schoolYears'],
    'admin/school-years/create' => ['controller' => 'AdminController', 'method' => 'createSchoolYear'],
    'admin/school-years/activate' => ['controller' => 'AdminController', 'method' => 'activateSchoolYear'],
    'admin/school-years/delete' => ['controller' => 'AdminController', 'method' => 'deleteSchoolYear'],
    'admin/binomages' => ['controller' => 'AdminController', 'method' => 'adminBinomages'],
    'api/notifications/unread-count' => ['controller' => 'NotificationApiController', 'method' => 'unreadCount'],
    'api/reactions' => ['controller' => 'ReactionApiController', 'method' => 'toggle'],
    'api/mentions/search' => ['controller' => 'MentionApiController', 'method' => 'search'],
    'api/tags/search' => ['controller' => 'TagApiController', 'method' => 'search'],
];

$guestRoutes = ['login', 'register', 'forgot-password', 'reset-password'];
$adminRoutes = [
    'admin', 'admin/dashboard', 'admin/users', 'admin/users/json', 'admin/users/edit',
    'admin/users/delete', 'admin/moderation', 'admin/moderation/action', 'admin/categories',
    'admin/filieres', 'admin/zones', 'admin/message-groups', 'admin/message-groups/update',
    'admin/categories/create-zone', 'admin/categories/create-category',
    'admin/tags/action', 'admin/settings', 'admin/school-years', 'admin/school-years/create',
    'admin/school-years/activate', 'admin/school-years/delete', 'admin/binomages',
];
$roleRoutes = [
    'dashboard/delegue' => 'delegue_general',
    'dashboard/chef-classe' => 'delegue_de_classe',
    'dashboard/chef_classe' => 'delegue_de_classe',
];

foreach ($routes as $path => &$definition) {
    if (!is_array($definition) || empty($definition['controller'])) {
        continue;
    }
    if (in_array($path, $guestRoutes, true)) {
        $definition['middleware'] = ['guest'];
    } elseif (in_array($path, $adminRoutes, true)) {
        $definition['middleware'] = ['admin'];
    } elseif (isset($roleRoutes[$path])) {
        $definition['middleware'] = ['role:' . $roleRoutes[$path]];
    } else {
        $definition['middleware'] = ['auth'];
    }
}
unset($definition);

return $routes;
