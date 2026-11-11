<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProductMediaController extends Controller
{
    public function show(string $filename): BinaryFileResponse|Response
    {
        if (! preg_match('/\A[a-zA-Z0-9][a-zA-Z0-9._-]{0,180}\.(?:jpg|jpeg|png|webp)\z/i', $filename)) {
            abort(404);
        }

        foreach (['produtos', 'products'] as $directory) {
            $path = realpath(base_path('gpm2/public/uploads/'.$directory.'/'.$filename));
            $root = realpath(base_path('gpm2/public/uploads/'.$directory));

            if ($path && $root && str_starts_with($path, $root.DIRECTORY_SEPARATOR) && is_file($path)) {
                return response()->file($path, [
                    'Cache-Control' => 'public, max-age=86400',
                    'X-Content-Type-Options' => 'nosniff',
                ]);
            }
        }

        abort(404);
    }
}
