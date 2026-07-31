<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;

class CategoryController extends PublicController
{
    public function show(string $slug): string
    {
        $categories = config('Portal')->categories;

        if (! isset($categories[$slug])) {
            throw PageNotFoundException::forPageNotFound();
        }

        return view('public/category', [
            'category' => $categories[$slug],
            'slug'     => $slug,
            'title'    => $categories[$slug]['name'],
        ]);
    }
}
