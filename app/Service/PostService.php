<?php

namespace App\Service;

use App\Models\Author;
use App\Models\Hashtag;
use App\Models\HashtagHasPost;
use App\Models\Post;
use App\Repositories\Eloquent\MediaImageEntryRepository;
use App\Repositories\Eloquent\PostRepository;
use Classes\Constants;
use Instagram\Media;
use Instagram\User;

class PostService extends BaseService {

    /** @var PostRepository */
    protected $repository;

    public function __construct()
    {
        $this->repository = app(PostRepository::class);
    }

    /**
     * @param $value
     * @param string $attribute
     * @return mixed
     */
    public function getPostAuthor($value, $attribute = 'id')
    {
        return $this->repository->getPostAuthor($value, $attribute);
    }

    /**
     * @param $advertiserId
     * @param $excludeHashtagCodes
     * @return mixed
     */
    public function getBannerPostIds($advertiserId, $excludeHashtagCodes) {

        return $this->repository->getBannerPostIds($advertiserId, $excludeHashtagCodes);
    }

    /**
     * @param $postId
     * @param $advertiserId
     * @return bool
     */
    private function isSyncedVideo($postId, $advertiserId)
    {
        /** @var MediaImageEntryRepository $mediaImageEntryRepository */
        $mediaImageEntryRepository = app(MediaImageEntryRepository::class);
        $result = $mediaImageEntryRepository->getFBImgEntryByPostId($postId, $advertiserId);
        if ($result) {
            return true;
        }

        return false;
    }

    /**
     * @param $postId
     * @return mixed
     */
    public function getPostWithAuthor($postId)
    {
        return $this->repository->getPostWithAuthor($postId);
    }

    /**
     * @param $postType
     * @return bool
     */
    public static function isAcceptablePostType($postType)
    {
        return in_array($postType, [Constants::IMG_TYPE_TEXT, Constants::VIDEO_TYPE_TEXT, Constants::CAROUSEL_TYPE_TEXT]);
    }

    /**
     * @param Hashtag $hashtag
     * @param Media $postData
     * @return array
     */
    public function storePostData(Media $postData, Hashtag $hashtag = null)
    {
        try {
            // Create or Update Author
            $authorData = $postData->getUser();
            $author     = $this->storeAuthorData($authorData);
            // Create or Update Post
            $postUpdateInfo = [
                'post_url'  => $postData->getLink(),
                'author_id' => $author->id,
                'pub_date'  => $postData->getCreatedTime('Y-m-d H:i:s'),
                'like'      => $postData->getLikesCount(),
                'text'      => $postData->getCaption() ? $postData->getCaption()->getText() : '',
                'comment'   => $postData->comments->count
            ];
            $postIds = [];

            if ($postData->getType() == Constants::CAROUSEL_TYPE_TEXT) {
                $materials = $postData->carousel_media;
                $index     = 1;
                foreach ($materials as $material) {
                    if ($material->type == Constants::IMG_TYPE_TEXT) {
                        $postUpdateInfo['image_url']   = $material->images->standard_resolution->url;
                        $postUpdateInfo['video_url']   = null;
                        $postUpdateInfo['file_format'] = Post::CAROUSEL_IMAGE;
                    } else {
                        $postUpdateInfo['image_url']   = $postData->getStandardResImage()->url;
                        $postUpdateInfo['video_url']   = $material->videos->standard_resolution->url;
                        $postUpdateInfo['file_format'] = Post::CAROUSEL_VIDEO;
                    }
                    $post = $this->createOrUpdate([
                        'post_id'   => $postData->getId(),
                        'carousel_no'  => $index
                    ], [
                        'post_id'     => $postData->getId(),
                        'carousel_no' => $index
                    ], $postUpdateInfo);

                    $index++;
                    $postIds[] = $post->id;
                    if (!empty($hashtag)) {
                        HashtagHasPost::createNew($hashtag->id, $post->id);
                    }
                }
            } else {
                if ($postData->getType() == Constants::IMG_TYPE_TEXT) {
                    $postUpdateInfo['image_url']   = $postData->getStandardResImage()->url;
                    $postUpdateInfo['video_url']   = null;
                    $postUpdateInfo['file_format'] = Post::IMAGE;
                } else {
                    $postUpdateInfo['video_url']   = $postData->getStandardResVideo()->url;
                    $postUpdateInfo['image_url']   = $postData->getStandardResImage()->url;
                    $postUpdateInfo['file_format'] = Post::VIDEO;
                }

                $post = $this->createOrUpdate([
                    'post_id' => $postData->getId()
                ], [
                    'post_id' => $postData->getId()
                ], $postUpdateInfo);
                $postIds[] = $post->id;
                if (!empty($hashtag)) {
                    HashtagHasPost::createNew($hashtag->id, $post->id);
                }
            }

            return $postIds;
        } catch (\Exception $e) {
            \Log::error($e);
        }

        return null;
    }

    /**
     * @param $authorData
     * @return Author
     */
    public function storeAuthorData(User $authorData)
    {
        $author = Author::where('username', $authorData->getUserName())->first();
        if (!$author) {
            $author = new Author();
        }
        $author->profile_url = 'https://www.instagram.com/' . $authorData->getUserName();
        $author->username = $authorData->getUserName();
        $author->save();

        return $author;
    }

    /**
     * @param $authorName
     * @return mixed
     */
    public function getPostsByAuthorName($authorName)
    {
        return $this->repository->getPostsByAuthorName($authorName);
    }


    /**
     * @param $mediaId
     * @return mixed
     */
    public function getPostsByMediaId($mediaId)
    {
        return $this->repository->findAllBy('post_id', $mediaId);
    }

    /**
     * @param $postIds
     * @return mixed
     */
    public function getPostWithOffers($postIds)
    {
        return $this->repository->getPostWithOffers($postIds);
    }
}