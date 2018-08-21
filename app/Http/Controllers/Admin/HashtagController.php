<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\CrawlHashtagPostsJob;
use App\Models\Hashtag;
use App\Repositories\Eloquent\HashtagRepository;
use App\Service\HashtagService;
use Classes\Constants;
use Illuminate\Http\Request;

class HashtagController extends Controller
{
    /**
     * @return \Illuminate\Contracts\View\View
     */
    public function index(HashtagService $hashtagService)
    {
        $hashtags = Hashtag::whereIn('active_flg', [Hashtag::ACTIVE, Hashtag::FAIL, Hashtag::WAIT])->get();
        $advertisers = $hashtagService->getHashtagAdvertiser();

        return view()->make('admin.hashtag_list', [
            'hashtags' => $hashtags,
            'advertisers' => $advertisers
        ]);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function executeCrawlCommand(Request $request)
    {
        $hashtagId = $request->input('id');
        try {
            $this->dispatch(new CrawlHashtagPostsJob($hashtagId, Hashtag::CRAW_LIMIT));
            $user = \Auth::guard('admin')->user();
            \Log::info("User ". $user->user_name . " start crawling hashtag id " . $hashtagId);
        } catch (\Exception $e) {
            \Log::error($e);
        }
        /** @var HashtagRepository $hashtagRepository */
        $hashtagRepository = app(HashtagRepository::class);
        $hashtagRepository->update(['active_flg' => Hashtag::WAIT], $hashtagId);

        return response()->json(200);
    }

    public function inactive(Request $request, HashtagService $hashtagService)
    {
        $hashtagId = $request->input('id');
        try {
            $hashtagService->updateModel(['active_flg' => Hashtag::UNACTIVE], $hashtagId);
            $request->session()->flash(Constants::INFO_MESSAGE, "Updated");

            return response()->json([]);
        } catch (\Exception $e) {
            \Log::error($e);
            $request->session()->flash(Constants::ERROR_MESSAGE, "Failed");

            return response()->json([], 400);
        }

    }
}
