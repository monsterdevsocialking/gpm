<?php

namespace App\Http\Controllers;

use App\Models\ContactMessage;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AdminContactController extends Controller
{
    public function index(Request $request): View
    {
        $messages = ContactMessage::query()
            ->latest('created_at')
            ->paginate(30);

        return view('admin.contacts.index', compact('messages'));
    }
}
