<?php

namespace Classes;

use App\Http\Requests\Request;
use App\UGCConfig;
use Illuminate\Database\Eloquent\Model;
use Instagram\Instagram;
use Instagram\Auth as InstagramAuth;

/**
 * App\Models\Api\InstagramApi
 *
 * @mixin \Eloquent
 */
class InstagramApiClient extends Model
{
    /** @var Instagram */
    public $instagram;

    /** @var InstagramAuth $auth */
    public $auth;

    public $config;

    public $token;

    /**
     * InstagramApiClient constructor.
     * @param string $redirect
     */
    public function __construct($redirect = '')
    {
        $this->config = UGCConfig::get('instagram');
        if ($redirect) {
            $this->config['redirect_uri'] .= '?redirect='.$redirect;
        }
        $this->instagram = new InstagramApi();
    }

    /**
     * @return InstagramAuth
     */
    public function getAuth()
    {
        if (!$this->auth) {
            $config = $this->config;
            $this->auth = new InstagramAuth($config);
        }
        return $this->auth;
    }

    /**
     *
     */
    public function goLoginUrl()
    {
        $this->getAuth()->authorize();
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param $token
     */
    public function setToken($token)
    {
        $this->token = $token;
        $this->instagram->setAccessToken($token);
    }

    /**
     * @param $code
     * @return string
     * @throws \Instagram\Core\ApiException
     */
    public function getTokenBySeed($code)
    {
        $this->token = $this->getAuth()->getAccessToken($code);
        return $this->token;
    }

    /** 認証
     * @param $code
     * @return \Instagram\CurrentUser
     */
    public function login($code)
    {
        $this->getTokenBySeed($code);
        $this->instagram->setAccessToken($this->token);
        $current_user = $this->instagram->getCurrentUser();
        return $current_user;
    }
    
    /**
     * @param $mediaId
     * @return \Instagram\Media
     */
    public function getMedia($mediaId)
    {
        return $this->instagram->getMedia($mediaId);
    }

    /**
     * @param $mediaId
     * @param null $maxId
     * @param int $limit
     * @return \Instagram\Media
     */
    public function getUserMedia($mediaId, $maxId = null, $limit = 50)
    {
        return $this->instagram->getUserMedia($mediaId, [
            'count' => $limit,
            'max_id' => $maxId
        ]);
    }

    /**
     * @param $hashtag
     * @param int $limit
     * @param null $maxTagId
     * @return \Instagram\Collection\TagMediaCollection
     */
    public function getTagMedia($hashtag, $maxTagId = null, $limit = 50)
    {
        return $this->instagram->getTagMedia($hashtag, [
            'count' => $limit,
            'max_tag_id' => $maxTagId
        ]);
    }

    /**
     * @param $userId
     * @return \Instagram\User
     */
    public function getUser($userId)
    {
        return $this->instagram->getUser($userId);
    }

    /**
     * @param $token
     * @return \Instagram\CurrentUser
     */
    public function getCurrentUserByToken($token)
    {
        $this->setToken($token);
        return $this->instagram->getCurrentUser();
    }
    
    
    /**
     * @param $accessToken
     * @return bool
     */
    public function isValidToken($accessToken)
    {
        $this->setToken($accessToken);
        try {
            $this->getCurrentUserByToken($accessToken);
            
            return true;
        } catch (\Exception $e) {

            return false;
        }
    }
}
