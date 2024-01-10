@extends('layouts.layout')

@section('content')
    @foreach($posts as $post)
        <h1>{{ $post->title  }}</h1>
    @endforeach
@endsection