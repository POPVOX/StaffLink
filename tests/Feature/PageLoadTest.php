<?php

test('it renders the chat interface', function () {
    $response = $this->get('/');
    expect($response->getContent())->toContain('Welcome to the StaffLink chatbot');
    $response->assertStatus(200);
});


test('it renders the resources page', function () {
    $response = $this->get('/resources');
    expect($response->getContent())->toContain('Resources Referenced');
    $response->assertStatus(200);
});

test('it renders the FAQ page', function () {
    $response = $this->get('/faq');
    expect($response->getContent())->toContain('Frequently asked questions');
    $response->assertStatus(200);
});

test('it renders the about page', function () {
    $response = $this->get('/about');
    expect($response->getContent())->toContain('open-source project');
    $response->assertStatus(200);
});

test('it renders the privacy page', function () {
    $response = $this->get('/privacy');
    expect($response->getContent())->toContain('No identifying information');
    $response->assertStatus(200);
});
