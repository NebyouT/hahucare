@extends('backend.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Dashboard Comments</h3>
                    @if(auth()->user()->can('add_dashboard_comment'))
                    <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#addCommentModal">
                        <i class="fas fa-plus"></i> Add Comment
                    </button>
                    @endif
                </div>
                <div class="card-body">
                    @if($comments->count() > 0)
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <th>Clinic</th>
                                    <th>Comment</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($comments as $comment)
                                <tr>
                                    <td>{{ $comment->user->name ?? 'N/A' }}</td>
                                    <td>{{ $comment->clinic->name ?? 'N/A' }}</td>
                                    <td>{{ Str::limit($comment->comment, 100) }}</td>
                                    <td>
                                        @if($comment->is_approved)
                                        <span class="badge badge-success">Approved</span>
                                        @else
                                        <span class="badge badge-warning">Pending</span>
                                        @endif
                                    </td>
                                    <td>{{ $comment->created_at->format('M d, Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('backend.dashboard-comments.show', $comment->id) }}" class="btn btn-sm btn-info">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if(auth()->user()->can('edit_own_dashboard_comment') && $comment->user_id === auth()->id())
                                        <button class="btn btn-sm btn-warning edit-comment" data-id="{{ $comment->id }}">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        @endif
                                        @if(auth()->user()->can('delete_own_dashboard_comment') && $comment->user_id === auth()->id())
                                        <button class="btn btn-sm btn-danger delete-comment" data-id="{{ $comment->id }}">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                        @endif
                                        @if(auth()->user()->can('moderate_dashboard_comments'))
                                        @if($comment->is_approved)
                                        <button class="btn btn-sm btn-secondary unapprove-comment" data-id="{{ $comment->id }}">
                                            <i class="fas fa-thumbs-down"></i>
                                        </button>
                                        @else
                                        <button class="btn btn-sm btn-success approve-comment" data-id="{{ $comment->id }}">
                                            <i class="fas fa-thumbs-up"></i>
                                        </button>
                                        @endif
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $comments->links() }}
                    @else
                    <div class="text-center py-4">
                        <p class="text-muted">No comments found.</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Comment Modal -->
@if(auth()->user()->can('add_dashboard_comment'))
<div class="modal fade" id="addCommentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Add Comment</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="addCommentForm">
                    @csrf
                    <div class="form-group">
                        <label for="comment">Comment</label>
                        <textarea class="form-control" id="comment" name="comment" rows="4" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="saveComment">Save</button>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Edit Comment Modal -->
<div class="modal fade" id="editCommentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Comment</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="editCommentForm">
                    @csrf
                    <input type="hidden" id="edit_comment_id" name="comment_id">
                    <div class="form-group">
                        <label for="edit_comment">Comment</label>
                        <textarea class="form-control" id="edit_comment" name="comment" rows="4" required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="updateComment">Update</button>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
$(document).ready(function() {
    // Add Comment
    $('#saveComment').click(function() {
        $.ajax({
            url: '{{ route('backend.dashboard-comments.store') }}',
            type: 'POST',
            data: $('#addCommentForm').serialize(),
            success: function(response) {
                $('#addCommentModal').modal('hide');
                $('#addCommentForm')[0].reset();
                location.reload();
            },
            error: function(xhr) {
                alert(xhr.responseJSON.error || 'Error adding comment');
            }
        });
    });

    // Edit Comment
    $('.edit-comment').click(function() {
        var commentId = $(this).data('id');
        $.get('{{ route('backend.dashboard-comments.show', ':id') }}'.replace(':id', commentId), function(data) {
            $('#edit_comment_id').val(commentId);
            $('#edit_comment').val($(data).find('#comment-text').text());
            $('#editCommentModal').modal('show');
        });
    });

    $('#updateComment').click(function() {
        var commentId = $('#edit_comment_id').val();
        $.ajax({
            url: '{{ route('backend.dashboard-comments.update', ':id') }}'.replace(':id', commentId),
            type: 'PUT',
            data: {
                _token: '{{ csrf_token() }}',
                comment: $('#edit_comment').val()
            },
            success: function(response) {
                $('#editCommentModal').modal('hide');
                location.reload();
            },
            error: function(xhr) {
                alert(xhr.responseJSON.error || 'Error updating comment');
            }
        });
    });

    // Delete Comment
    $('.delete-comment').click(function() {
        if(confirm('Are you sure you want to delete this comment?')) {
            var commentId = $(this).data('id');
            $.ajax({
                url: '{{ route('backend.dashboard-comments.destroy', ':id') }}'.replace(':id', commentId),
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    location.reload();
                },
                error: function(xhr) {
                    alert(xhr.responseJSON.error || 'Error deleting comment');
                }
            });
        }
    });

    // Approve Comment
    $('.approve-comment').click(function() {
        var commentId = $(this).data('id');
        $.ajax({
            url: '{{ route('backend.dashboard-comments.approve', ':id') }}'.replace(':id', commentId),
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert(xhr.responseJSON.error || 'Error approving comment');
            }
        });
    });

    // Unapprove Comment
    $('.unapprove-comment').click(function() {
        var commentId = $(this).data('id');
        $.ajax({
            url: '{{ route('backend.dashboard-comments.unapprove', ':id') }}'.replace(':id', commentId),
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                location.reload();
            },
            error: function(xhr) {
                alert(xhr.responseJSON.error || 'Error unapproving comment');
            }
        });
    });
});
</script>
@endsection
@endsection
