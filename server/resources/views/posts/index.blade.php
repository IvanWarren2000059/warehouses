<!-- resources/views/posts/index.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="d-flex justify-content-end">
        <a href="{{ route('posts.create') }}" class="btn btn-primary">Create New Post</a>

        </div>

        <table class="table-striped">
            <thead>
                <tr>
                    <th width="25%">Title</th>
                    <th width="20%">Created At</th>
                    <th width="25%">Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Loop through all the posts -->
                @foreach($posts as $post)
                    <tr>
                        <td>{{ $post->title }}</td>
                        <td>{{ $post->created_at->format('M d, Y H:i:s') }}</td>
                        <td>
                            <a href="{{ route('show', $post->id) }}" class="btn btn-primary mr-2 btn-sm">View</a>
                            <a href="{{ route('edit', $post->id) }}" class="btn btn-secondary mr-2 btn-sm">Edit</a>
                            <form action="{{ route('delete', $post->id) }}" method="POST" style="display:inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger mr-2 btn-sm" onclick="return confirm('Are you sure you want to delete this post?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>


@endsection
