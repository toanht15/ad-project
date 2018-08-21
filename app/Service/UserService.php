<?php


namespace App\Service;


use App\Models\InvitationCode;
use App\Repositories\Eloquent\InvitationCodeRepository;
use App\Repositories\Eloquent\UserAdvertiserRepository;
use App\Repositories\Eloquent\UserRepository;
use Carbon\Carbon;
use Classes\Roles;

class UserService extends BaseService {

    /** @var UserRepository  */
    protected $repository;

    public function __construct(UserRepository $userRepository)
    {
        $this->repository = $userRepository;
    }

    /**
     * @param $advertiserId
     * @return mixed
     */
    public function getUserByAdvertiserId($advertiserId)
    {
        return $this->repository->getUserByAdvertiserId($advertiserId);
    }

    /**
     * @param array $attributes
     * @return mixed
     */
    public function findAdmin(array $attributes)
    {
        $attributes['role'] = Roles::ADMIN;
        return $this->repository->queryWhere($attributes);
    }

    /**
     * @param array $userData
     * @param array $invitationCodeData
     * @param $advertiserId
     * @param $role
     * @return array[user, invitation code]
     */
    public function createUserWithInvitationCode(array $userData, array $invitationCodeData, $advertiserId, $role)
    {
        $userData['role'] = Roles::USER;
        try {
            /** @var InvitationCodeService $invitationCodeService */
            $invitationCodeService = app(InvitationCodeService::class);
            /** @var UserAdvertiserRepository $userAdvertiserRepository */
            $userAdvertiserRepository = app(UserAdvertiserRepository::class);

            \DB::beginTransaction();
            if (isset($userData['id']) && $userData['id']) {
                $user = $this->findModel($userData['id']);
            } else {
                unset($userData['id']);
                $user = $this->createModel($userData);
            }

            $userAdvertiserData = [
                'user_id' => $user->id,
                'advertiser_id' => $advertiserId
            ];
            $userAdvertiserRepository->createOrUpdate($userAdvertiserData, $userAdvertiserData, ['role' => $role]);

            $invitationCodeData['code'] = $invitationCodeService->generateRandomCode();
            $invitationCodeData['user_id'] = $user->id;
            $invitationCodeData['expired_date'] = Carbon::now()->addDays(InvitationCode::EXPIRE_DAYS)->toDateTimeString();
            $invitationCode = $invitationCodeService->createModel($invitationCodeData);
            \DB::commit();

            return [$user, $invitationCode];
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error($e);
        }

        return [null, null];
    }

    /**
     * @param array $userData
     * @param array $invitationCodeData
     * @return array
     */
    public function createAdminWithInvitationCode(array $userData, array $invitationCodeData)
    {
        $userData['role'] = Roles::ADMIN;
        try {
            /** @var InvitationCodeService $invitationCodeService */
            $invitationCodeService = app(InvitationCodeService::class);

            \DB::beginTransaction();
            if (isset($userData['id']) && $userData['id']) {
                $user = $this->findModel($userData['id']);
            } else {
                unset($userData['id']);
                $user = $this->createModel($userData);
            }

            $invitationCodeData['code'] = $invitationCodeService->generateRandomCode();
            $invitationCodeData['user_id'] = $user->id;
            $invitationCodeData['expired_date'] = Carbon::now()->addDays(InvitationCode::EXPIRE_DAYS)->toDateTimeString();
            $invitationCode = $invitationCodeService->createModel($invitationCodeData);
            \DB::commit();

            return [$user, $invitationCode];
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error($e);
        }

        return [null, null];
    }
    
    public function changeEGCSetting($id) {
        return $this->repository->changeEGCSetting($id);
    }
}