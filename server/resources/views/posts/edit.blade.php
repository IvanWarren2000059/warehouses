<!-- resources/views/posts/edit.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Edit Post</h1>
        <form action="{{ route('update', $post->id) }}" method="POST">
            <!-- the @csrf and method are Laravel directives that prevent Cross-Site Request Forgery attacks -->
            @csrf
            @method('PUT')
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" class="form-control" id="title" name="title" value="{{ $post->title }}">
            </div>
            <div class="form-group">
                <label for="content">Content</label>
                <textarea class="form-control" id="body" name="body" rows="5">{{ $post->body }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Update</button>
        </form>
    </div>
@endsection
