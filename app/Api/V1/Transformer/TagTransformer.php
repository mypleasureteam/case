<?php namespace Mypleasure\Api\V1\Transformer;

use Mypleasure\Tag;
use Mypleasure\Video;
use Mypleasure\Collection;
use Mypleasure\Api\V1\Transformer\VideoTransformer;
use League\Fractal\TransformerAbstract;

class TagTransformer extends TransformerAbstract {

  protected $availableIncludes = [
    'videos'
  ];

  public function transform(Tag $tag)
  {
    return [
      'id'     => (int) $tag->id,
      'name'   => $tag->name,
      'slug'   => $tag->slug,
      'links'  => [
        'self' => ['rel' => 'self', 'uri' => '/tags/' . $tag->id]
      ]
    ];
  }

  public function includeVideos(Tag $tag)
  {
    $videos = $tag->videos;
    $user = \JWTAuth::parseToken()->toUser();

    $filtered = $videos->reject(function ($video) use ($user) {
      return ($video->getOwner()->id !== $user->id && $video->naughty == true);
    });

    return $this->collection($filtered, new VideoTransformer);
  }

}