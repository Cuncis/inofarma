<?php

return [

    /*
    | How long an admin reply may sit unseen before it is emailed to the
    | visitor. A visitor with the chat window open sees the reply within seconds
    | and is never emailed; one who closed the browser gets it by email after
    | this many minutes. Needs a running queue worker (the delay is a queued job).
    */
    'email_delay_minutes' => (int) env('CHAT_EMAIL_DELAY_MINUTES', 2),

];
