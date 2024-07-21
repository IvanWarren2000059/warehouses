<!-- resources/views/posts/create.blade.php -->

@extends('layouts.app')

@section('content')
    <div class="container">
        <h1>Create New Post</h1>
        <form action="{{ route('create') }}" method="POST">
            <!-- the @csrf directive is used to prevent cross-site request forgery -->
            @csrf
            <div class="form-group">
                <label for="title">Title</label>
                <input type="text" class="form-control" id="title" name="title">
            </div>
            <div class="form-group">
                <label for="content">Content</label>
                <textarea class="form-control" id="body" name="body" rows="5"></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
        </form>
    </div>
@endsection
