<?php

namespace App\Http\Controllers;

use App\Http\Requests\Request;
use App\Repositories\Eloquent\PostRepository;
use App\Service\InstagramAccountService;
use App\Service\MediaAccountService;
use App\Service\PartService;
use App\Service\PostService;

class PostController extends Controller
{
    /**
     * @param InstagramAccountService $instagramAccountService
     * @param MediaAccountService $mediaAccountService
     * @param PostService $postService
     * @param $id
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function detail(InstagramAccountService $instagramAccountService, MediaAccountService $mediaAccountService, PostService $postService, $id)
    {
        $advertiser = \Auth::guard('advertiser')->user();
        $mediaAccounts = $mediaAccountService->getMediaAccountsWithToken($advertiser->id);
        $post = $postService->getPostWithAuthor($id);

        $instagramAccount = $instagramAccountService->getInstagramAccountByAdvertiserId($advertiser->id)->first();

        return view('post.detail', [
            'post' => $post,
            'mediaAccounts' => $mediaAccounts,
            'instagramAccount' => $instagramAccount,
        ]);
    }

    /**
     * @param PartService $partService
     * @param $postId
     * @return \Illuminate\Http\JsonResponse
     * @throws \App\Exceptions\APIRequestException
     * @throws \Classes\Parts\Exceptions\JsonParseException
     */
    public function apiGetRegisteredPart(PartService $partService, $postId)
    {
        $parts = $partService->getRegisteredPart($postId);

        return response()->json($parts);
    }
}
