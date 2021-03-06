@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Dashboard</div>

                <div class="card-body text-center">
                    @if (session('status'))
                    <div class="alert alert-success">
                        {{ session('status') }}
                    </div>
                    @endif
                    You are logged in!
                </div>
                <div class="content text-center">
                    <div class="links btn">
                        @auth
                        <a href="/drive" class="btn btn-danger">List Google Drive Files & Upload Files</a>
                        @else
                        <a href="/login/google">Login With Google</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection