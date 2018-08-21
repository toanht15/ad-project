<?php


namespace App\Service;

use App\Repositories\Eloquent\HashtagRepository;
use App\Models\Hashtag;

class HashtagService extends BaseService {
    /** @var HashtagRepository */
    protected $repository;

    public function __construct()
    {
        $this->repository = app(HashtagRepository::class);
    }

    /**
     * @return array
     */
    public function getHashtagAdvertiser()
    {
        $result = [];
        $hashtags = $this->repository->getActiveHashtags();
        foreach ($hashtags as $hashtag) {
            $advertisers = $this->repository->getHashtagAdvertiser($hashtag->id);
            foreach ($advertisers as $advertiser) {
                if (isset($result[$advertiser->id]) && in_array($advertiser->name, $result[$advertiser->id])) {
                    continue;
                }
                $result[$advertiser->id][] = $advertiser->name;
            }
        }

        return $result;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function getHashtagInstagramAccount($id)
    {
        return $this->repository->getHashtagInstagramAccount($id);
    }

    /**
     * @param null $id
     * @return mixed
     */
    public function getShouldCrawlHashtag($id = null)
    {
        $hashtags = $this->repository->getHashtag($id, null, Hashtag::TYPE_HASHTAG, [Hashtag::ACTIVE, Hashtag::FAIL, Hashtag::WAIT]);
        return $hashtags;
    }

    /**
     * @param $hashtagId
     * @param array $contractTypes
     * @return bool
     */
    public function hasActiveContract($hashtagId, array $contractTypes)
    {
        $now = (new \DateTime())->format('Y-m-d');
        $advertisers = $this->repository->getHashtagAdvertiserWithContract($hashtagId, $contractTypes);
        if (!$advertisers) {
            return false;
        }

        foreach ($advertisers as $advertiser) {
            $endDate = (new \DateTime($advertiser->end_date))->modify('+7 days')->format('Y-m-d');
            if (check_date_in_range($advertiser->start_date, $endDate, $now) == 0) {
                return true;
            };
        }

        return false;
    }
}