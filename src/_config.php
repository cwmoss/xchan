<?php

return [
    xchan\auth::class => function (xchan\auth\store $store, string $auth_views, string $X_AUTH_SECRET) {
        return new xchan\auth($store, 'xchan', $X_AUTH_SECRET, ['views' => $auth_views]);
    },

    'auth_views' => fn (): string => __DIR__ . '/../resources',
    'templates' => fn (): string => __DIR__ . '/../resources',
    'sqlitedb' => fn (): string => __DIR__ . '/../var/app.db',
    xchan\configuration::class => function (string $templates) use ($broadcast) {
        return new xchan\configuration($templates, $broadcast);
    },
    Clue\React\SQLite\DatabaseInterface::class => function (string $sqlitedb) {
        return (new Clue\React\SQLite\Factory)->openLazy($sqlitedb);
    }
    //  \xchan\auth\store => function(xchan\sqlite $db)
];
