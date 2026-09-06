<?php

test('guest is redirected to the login page', function () {
    $this->get('/')->assertRedirect('/login');
});
