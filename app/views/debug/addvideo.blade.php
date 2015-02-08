@extends('master')

@section('content')

  <div class="container">
    @if (Session::has('message'))
      <div class="alert alert-info">{{{ Session::get('message') }}}</div>
    @endif

    <h3 class="col-sm-12 col-md-12 col-lg-12">{{{ $user->username }}} (add fake video)</h3>

    <div class="col-sm-12 col-md-12 col-lg-12">
      <p>Add a fake video to the current user, for development/testing purposes.</p>
    </div>

    <div class="col-sm-12 col-md-12 col-lg-12">
      {{ Form::open(array('url' => '/me/videos/add/debug')) }}
        {{ Form::submit('Add fake video', array('class' => 'btn btn-primary')) }}
      {{ Form::close() }}
    </div>
  </div>

@stop