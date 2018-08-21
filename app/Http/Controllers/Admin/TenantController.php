<?php

namespace App\Http\Controllers\Admin;

use App\Models\Tenant;
use App\Service\TenantService;
use Classes\Constants;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class TenantController extends Controller
{
    /**
     * @param TenantService $tenantService
     * @return \Illuminate\Contracts\View\View
     */
    public function listPage(TenantService $tenantService)
    {
        $tenants = $tenantService->all();

        return view()->make('admin.tenant_list', [
            'tenants' => $tenants
        ]);
    }

    /**
     * @param Request $request
     * @param TenantService $tenantService
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create(Request $request, TenantService $tenantService)
    {
        $this->validate($request, [
            'name' => 'required'
        ]);

        $tenantService->createModel([
            'name' => $request->input('name'),
            'created_admin_id' => \Auth::guard('admin')->user()->id
        ]);

        $request->session()->flash(Constants::INFO_MESSAGE, 'テナントを作成しました。');

        return back();
    }
}
