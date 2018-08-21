<?php


namespace App\Service;


use App\Repositories\Eloquent\InvitationCodeRepository;

class InvitationCodeService extends BaseService {

    /** @var InvitationCodeRepository  */
    protected $repository;

    public function __construct(InvitationCodeRepository $invitationCodeRepository)
    {
        $this->repository = $invitationCodeRepository;
    }

    /**
     * @param $codeStr
     * @return mixed
     * @throws \Exception
     */
    public function getValidCode($codeStr)
    {
        $code = $this->repository->findBy('code', $codeStr);
        // check invalid code
        if (!$code || $code->is_used_flg || $code->expired_date < (new \DateTime())->format('Y-m-d H:i:s')) {
            throw new \Exception('招待URLの有効期限が切れているか、アカウントへのアクセス権がありません。<br>
            管理者にご確認ください。');
        }

        return $code;
    }

    /**
     * @param int $length
     * @return string
     */
    public static function generateRandomCode($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }
}