@extends('master')

@section('content')

  @if (Session::has('message'))
    <div class="alert alert-info">{{{ Session::get('message') }}}</div>
  @elsif (Session::has('message_fallback')
    <div class="alert alert-info">{{{ Session::get('message_fallback') }}}</div>
    <?php Session::forget('message_fallback'); ?>
  @endif

  <h3 class="col-sm-12 col-md-12 col-lg-12">{{{ $user->username }}} {{ Lang::get('videos.delete.title') }}</h3>

  <div class="row">

    <div class="col-sm-12 col-md-6 col-lg-6 video">
      <img class="col-sm-12 col-md-12 col-lg-12" src="{{ $video->poster }}" width="100%">
      <h5 class="col-sm-12 col-md-12 col-lg-12">{{{ $video->title }}}</h5>
      <div class="col-sm-12 col-md-12 col-lg-12">{{{ $video->duration }}}</div>
    </div>

    <?php $url = URL::secure("/me/videos/{$video->id}/delete") ?>
    {{ Form::open(array('url' => $url)) }}
      {{ Form::hidden('video', $video->id) }}
      <div class="col-sm-12 col-md-6 col-lg-6">
        <h4>{{ Lang::get('videos.delete.areyousure') }}</h4>
        <a href="{{{ URL::secure('/me/videos') }}}" class="btn btn-default">{{ Lang::get('videos.delete.form.cancel') }}</a>
        <input type="submit" class="btn btn-primary" value="{{ Lang::get('videos.delete.form.delete') }}">
      </div>
    {{ Form::close() }}

  </div>

@stop