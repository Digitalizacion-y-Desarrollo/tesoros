<?php

namespace App\Controllers;

class Home extends PublicController
{
    public function index(): string
    {
        return view('public/home', [
            'categories' => config('Portal')->categories,
            'title'      => 'Tesoros Gastronómicos del Estado de México',
        ]);
    }
}
