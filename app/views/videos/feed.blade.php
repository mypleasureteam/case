@extends('master')

@section('content')

  @if (Session::has('message'))
  <div class="alert alert-info">{{{ Session::get('message') }}}</div>
  @endif

  <div class="row">
    <h3 class="col-sm-12 col-md-12 col-lg-12">Feed</h3>
    <span class="col-sm-12 col-md-12 col-lg-12">All public videos from all users</span>
  </div>

  <div class="row">
    @foreach($videos as $i => $v)
    <div class="col-sm-12 col-md-3 col-lg-3 video" data-video="{{ $v->video->embed_url }}" data-index="<?php echo $i ?>" style="top:30px;height:330px">
      @if ($v->video->method === '_dummy')
      <div class="col-sm-12 col-md-12 col-lg-12 dummy" style="display:block;width:100%;height:200px;background:#CCC"></div>
      @else
      <img class="col-sm-12 col-md-12 col-lg-12" src="{{ $v->video->poster }}" width="100%">
      @endif

      <h5 class="col-sm-12 col-md-12 col-lg-12">{{{ $v->video->title }}}</h5>
      <div class="col-sm-12 col-md-12 col-lg-12">{{{ $v->video->duration }}}</div>
      <div class="col-sm-12 col-md-12 col-lg-12">curated by {{{ $v->user->username }}}</div>
      <ul class="col-sm-12 col-md-12 col-lg-12">
        <li><a class="play" data-index="<?php echo $i ?>" href="#">Play video</a></li>
      </ul>
    </div>
    @endforeach
  </div>

    <div class="modal fade" id="player" tabindex="-1" role="dialog" aria-labelledby="player" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <div class="modal-header">
          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
          <h4 class="modal-title" id="player-label">Hello</h4>
          </div>
          <div class="modal-body">
              <div id="embed-body"></div>
          </div>
          <div class="modal-footer">
              <button type="button" class="btn btn-primary prev-btn"><<</button>
              <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
              <button type="button" class="btn btn-primary next-btn">>></button>
          </div>
      </div>
    </div>
  </div>

  <script>
  $(function() {
    var $modal = $('#player'),
        $label = $('#player-label'),
        $body = $('#embed-body'),
        $playBtns = $('.play'),
        $prevBtn = $('.prev-btn'),
        $nextBtn = $('.next-btn'),
        embeds = [];
        currentIndex = 0,
        iframe = null;

    function init() {
      var $videos = $('.video');

      _.each($videos, function iter(video) {
        embeds.push($(video).attr('data-video'));
      });

      $playBtns.bind('click', openModal);
      $prevBtn.bind('click', prevVideo);
      $nextBtn.bind('click', nextVideo);
      $modal.bind('hide.bs.modal', closeModal);
    }

    function makeIframe(embed) {
      var iframe = '<iframe width="565" height="300" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="' + embed + '"></iframe>';
      $body.html(iframe);
    }

    function openModal(e) {
      e.preventDefault();

      currentIndex = parseInt($(e.target).attr('data-index'));
      embed = embeds[currentIndex];
      makeIframe(embed);

      var options = {
        'backdrop': true,
        'keyboard': true
      };
      $modal.modal(options);
    }

    function closeModal(e) {
      iframe = null;
      $body.html('');
    }

    function prevVideo() {
      var previous = currentIndex === 0 ? embeds.length - 1 : currentIndex - 1;
      embed = embeds[previous];
      makeIframe(embed);
      currentIndex = previous;
    }

    function nextVideo() {
      var next = currentIndex === embeds.length - 1 ? 0 : currentIndex + 1;
      embed = embeds[next];
      makeIframe(embed);
      currentIndex = next;
    }

    init();
  });
  </script>

@stop