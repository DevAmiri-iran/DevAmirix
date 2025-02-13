<?php

namespace App\System;

use Symfony\Component\HttpFoundation\Session\Attribute\AttributeBag;
use Symfony\Component\HttpFoundation\Session\Session as SessionMain;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;

trait Session
{
    /**
     * Starts a new session or resumes the existing one.
     *
     * @return SessionMain Returns an instance of SessionMain.
     * @throws \Exception
     */
    public static function start_session(): SessionMain
    {
        $options = config('session');
        $session = $options['session'];
        $handler = $options['handler'];
        $metadataBag = $options['metadataBag'];

        if (session_status() !== PHP_SESSION_ACTIVE) {
            $storage = new NativeSessionStorage($session, $handler, $metadataBag);
            $session = new SessionMain($storage, new AttributeBag('DevAmiri_sf2_attributes'));
            $session->start();
        } else {
            $storage = new NativeSessionStorage($session);
            $session = new SessionMain($storage, new AttributeBag('DevAmiri_sf2_attributes'));
        }

        $currentUserAgentHash = hash('sha256', $_SERVER['HTTP_USER_AGENT']);

        if ($session->has('user_agent_hash')) {
            if ($session->get('user_agent_hash') !== $currentUserAgentHash) {
                session_unset();
                session_destroy();
                session_commit();

                refresh();
            }
        } else {
            $session->set('user_agent_hash', $currentUserAgentHash);
        }

        return $session;
    }
}