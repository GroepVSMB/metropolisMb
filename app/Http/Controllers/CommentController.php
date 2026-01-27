<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CommentController extends Controller
{
    public function index()
    {
        // Haal alle comments op voor het grid
        return response()->json(Comment::with('user')->get());
    }

public function store(Request $request)
    {
        $request->validate([
            'grid_index' => 'required|integer',
            'content' => 'required|string|max:500',
        ]);

        $comment = Comment::create([
            'user_id' => Auth::id(),
            'grid_index' => $request->grid_index,
            
            // FOUT: 'content' => $request->content, 
            // GOED: Gebruik input() om botsing met de interne variabele te voorkomen
            'content' => $request->input('content'),
        ]);

        return response()->json($comment->load('user'));
    }

    public function resolve(Comment $comment)
    {
        // Alleen Planners (en Admins) mogen resolven
        // Of de auteur zelf? User Story zegt: "Planners can mark as resolved"
        
        $comment->update(['is_resolved' => !$comment->is_resolved]);
        return response()->json($comment);
    }

    public function destroy(Comment $comment)
    {
        // Alleen de auteur mag verwijderen
        if (Auth::id() !== $comment->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $comment->delete();
        return response()->json(['success' => true]);
    }
}