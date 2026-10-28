<?php

it('sends the root url to the menu page', function () {
    $this->get('/')->assertRedirect(route('menu'));
});
