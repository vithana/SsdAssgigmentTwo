@extends('layouts.app')
@section('content')
<div class="container">
    <h1 class="align-self-center">
        Upload Your Files To Your Google Drive
    </h1>
    <form action="/drive/upload" method="post" enctype="multipart/form-data" class="mb-5 mt-4">
        <input type="file" name="file">
        <button class="btn btn-success" type="submit">Submit</button>
        {{csrf_field()}}
    </form>
    @for ($i = count($files); $i >= 1; $i--)
    <div class="btn-outline-primary mt-2">
        {{$files[$i-1]}}
    </div>

    @endfor
</div>
@endsection