<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class HomeController extends Controller
{
    public function index()
    {
        return response()->json([
            "heroSlides" => [
                [
                    "title" => "Events & Celebrations",
                    "description" => "Capture unforgettable moments with photographer photographers.",
                    "image" => "/images/hero-events.jpg"
                ],
                [
                    "title" => "Wedding Photography",
                    "description" => "Timeless memories captured beautifully.",
                    "image" => "/images/hero-wedding.jpg"
                ],
                [
                    "title" => "Portrait Sessions",
                    "description" => "Photographer portraits for every occasion.",
                    "image" => "/images/hero-portrait.jpg"
                ]
            ],

            "categories" => [
                [
                    "name" => "Events",
                    "image" => "/images/hero-events.jpg"
                ],
                [
                    "name" => "Portrait",
                    "image" => "/images/hero-portrait.jpg"
                ],
                [
                    "name" => "Wedding",
                    "image" => "/images/hero-wedding.jpg"
                ]
            ],

            "featuredStudios" => []
        ]);
    }
}