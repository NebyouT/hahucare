<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Controller;
use App\Models\DashboardComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class DashboardCommentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:view_dashboard_comments')->only(['index', 'show']);
        $this->middleware('permission:add_dashboard_comment')->only(['store']);
        $this->middleware('permission:edit_own_dashboard_comment')->only(['update']);
        $this->middleware('permission:delete_own_dashboard_comment')->only(['destroy']);
        $this->middleware('permission:moderate_dashboard_comments')->only(['approve', 'unapprove']);
    }

    public function index(Request $request)
    {
        $query = DashboardComment::with(['user', 'clinic'])->approved();

        if (Auth::user()?->hasRole('clinic_admin')) {
            $query->where('clinic_id', Auth::user()->clinic_id);
        }

        if ($request->has('clinic_id')) {
            $query->where('clinic_id', $request->clinic_id);
        }

        $comments = $query->latest()->paginate(20);

        return view('backend.dashboard-comments.index', compact('comments'));
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'comment' => 'required|string|max:1000',
            'commentable_type' => 'nullable|string',
            'commentable_id' => 'nullable|integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $comment = DashboardComment::create([
            'user_id' => Auth::id(),
            'clinic_id' => Auth::user()->clinic_id,
            'comment' => $request->comment,
            'commentable_type' => $request->commentable_type,
            'commentable_id' => $request->commentable_id,
            'is_approved' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Comment added successfully',
            'comment' => $comment->load('user'),
        ]);
    }

    public function update(Request $request, $id)
    {
        $comment = DashboardComment::findOrFail($id);

        if ($comment->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'comment' => 'required|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        $comment->update(['comment' => $request->comment]);

        return response()->json([
            'success' => true,
            'message' => 'Comment updated successfully',
            'comment' => $comment->load('user'),
        ]);
    }

    public function destroy($id)
    {
        $comment = DashboardComment::findOrFail($id);

        if ($comment->user_id !== Auth::id() && !Auth::user()->hasRole('admin')) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $comment->update(['deleted_by' => Auth::id()]);
        $comment->delete();

        return response()->json([
            'success' => true,
            'message' => 'Comment deleted successfully',
        ]);
    }

    public function approve($id)
    {
        $comment = DashboardComment::findOrFail($id);
        $comment->update(['is_approved' => true]);

        return response()->json([
            'success' => true,
            'message' => 'Comment approved successfully',
        ]);
    }

    public function unapprove($id)
    {
        $comment = DashboardComment::findOrFail($id);
        $comment->update(['is_approved' => false]);

        return response()->json([
            'success' => true,
            'message' => 'Comment unapproved successfully',
        ]);
    }

    public function show($id)
    {
        $comment = DashboardComment::with(['user', 'clinic'])->findOrFail($id);
        return view('backend.dashboard-comments.show', compact('comment'));
    }
}
