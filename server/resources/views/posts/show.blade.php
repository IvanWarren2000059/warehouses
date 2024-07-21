<!-- resources/views/posts/show.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>{{ $post->title }}</h1>
        <p><strong>Author:</strong> {{ $post->user->name }}</p>
        <p><strong>Created At:</strong> {{ $post->created_at->format('M d, Y H:i:s') }}</p>
        <p>{{ $post->body }}</p>
        <a href="{{ route('edit', $post->id) }}" class="btn btn-primary">Edit</a>
        <form action="{{ route('delete', $post->id) }}" method="POST" style="display:inline">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this post?')">Delete</button>
        </form>
    </div>
@endsection
