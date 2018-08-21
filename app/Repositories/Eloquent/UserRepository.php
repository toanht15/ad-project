<?php


namespace App\Repositories\Eloquent;


use App\Models\User;

class UserRepository extends BaseRepository {

    /**
     * @return mixed
     */
    public function modelClass() {

        return User::class;
    }

    /**
     * @param $advertiserId
     * @return mixed
     */
    public function getUserByAdvertiserId($advertiserId)
    {
        return $this->model->join('user_advertisers', 'user_advertisers.user_id', '=', 'users.id')
            ->select('users.*')
            ->distinct('users.id')
            ->where('advertiser_id', $advertiserId)
            ->get();
    }
    
    public function changeEGCSetting($id) {
        $user = $this->model->find($id);
        
        $user->is_egc_staff = empty($user->is_egc_staff) ? 1 : 0;
        $user->save();
        
        return $user;
    }
}