@extends('backend.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Comment Details</h3>
                    <a href="{{ route('backend.dashboard-comments.index') }}" class="btn btn-secondary btn-sm float-right">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th width="30%">User</th>
                                    <td>{{ $comment->user->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Clinic</th>
                                    <td>{{ $comment->clinic->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td>
                                        @if($comment->is_approved)
                                        <span class="badge badge-success">Approved</span>
                                        @else
                                        <span class="badge badge-warning">Pending</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Created At</th>
                                    <td>{{ $comment->created_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                                @if($comment->updated_at)
                                <tr>
                                    <th>Updated At</th>
                                    <td>{{ $comment->updated_at->format('M d, Y H:i:s') }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                        <div class="col-md-12 mt-3">
                            <h5>Comment</h5>
                            <div class="card">
                                <div class="card-body" id="comment-text">
                                    {{ $comment->comment }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-footer">
                    @if(auth()->user()->can('edit_own_dashboard_comment') && $comment->user_id === auth()->id())
                    <button class="btn btn-warning edit-comment-btn" data-id="{{ $comment->id }}">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    @endif
                    @if(auth()->user()->can('delete_own_dashboard_comment') && $comment->user_id === auth()->id())
                    <button class="btn btn-danger delete-comment-btn" data-id="{{ $comment->id }}">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                    @endif
                    @if(auth()->user()->can('moderate_dashboard_comments'))
                    @if($comment->is_approved)
                    <button class="btn btn-secondary unapprove-comment-btn" data-id="{{ $comment->id }}">
                        <i class="fas fa-thumbs-down"></i> Unapprove
                    </button>
                    @else
                    <button class="btn btn-success approve-comment-btn" data-id="{{ $comment->id }}">
                        <i class="fas fa-thumbs-up"></i> Approve
                    </button>
                    @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

@section('scripts')
<script>
$(document).ready(function() {
    // Edit Comment
    $('.edit-comment-btn').click(function() {
        var commentId = $(this).data('id');
        $('#edit_comment_id').val(commentId);
        $('#edit_comment').val($('#comment-text').text().trim());
        $('#editCommentModal').modal('show');
    });

    // Delete Comment
    $('.delete-comment-btn').click(function() {
        if(confirm('Are you sure you want to delete this comment?')) {
            var commentId = $(this).data('id');
            $.ajax({
                url: '{{ route('backend.dashboard-comments.destroy', ':id') }}'.replace(':id', commentId),
                type: 'DELETE',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    window.location.href = '{{ route('backend.dashboard-comments.index') }}';
                },
                error: function(xhr) {
                    alert(xhr.responseJSON.error || 'Error deleting comment');
                }
            });
        }
    });

    // Approve Comment
    $('.approve-comment-btn').click(function() {
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
    $('.unapprove-comment-btn').click(function() {
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
