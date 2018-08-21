<?php

namespace App\Repositories\Eloquent;

use App\Models\PartImagesTemporary;

class PartImageTemporaryRepository extends BaseRepository {
    public function modelClass()
    {
        return PartImagesTemporary::class;
    }

    /**
     * @param $postId
     * @param $siteId
     * @return mixed
     */
    public function getPartIds($postId, $siteId)
    {
        return $this->model->where([
            'post_id' => $postId,
            'vtdr_site_id' => $siteId
        ])->select('vtdr_part_id')
            ->groupBy('vtdr_part_id')
            ->get();
    }

    /**
     * @param $postId
     * @param $siteId
     * @param $partIds
     * @return mixed
     */
    public function getRegiteredParts($postId, $siteId, $partIds)
    {
        return $this->model->where([
            'post_id' => $postId,
            'vtdr_site_id' => $siteId
        ])->whereIn('vtdr_part_id', $partIds)
            ->get();
    }

    /**
     * @param $date
     * @return mixed
     */
    public function getOldVtdrSites($date)
    {
        return $this->model->where('created_at', '<=', $date)->groupBy('vtdr_site_id')->get();
    }

}