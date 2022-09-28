<?php

namespace river\Http\Controllers\Admin\Nests;

use Illuminate\View\View;
use river\Models\Egg;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use river\Http\Controllers\Controller;
use river\Services\Eggs\Scripts\InstallScriptService;
use river\Contracts\Repository\EggRepositoryInterface;
use river\Http\Requests\Admin\Egg\EggScriptFormRequest;

class EggScriptController extends Controller
{
    /**
     * @var \Prologue\Alerts\AlertsMessageBag
     */
    protected $alert;

    /**
     * @var \river\Services\Eggs\Scripts\InstallScriptService
     */
    protected $installScriptService;

    /**
     * @var \river\Contracts\Repository\EggRepositoryInterface
     */
    protected $repository;

    /**
     * EggScriptController constructor.
     */
    public function __construct(
        AlertsMessageBag $alert,
        EggRepositoryInterface $repository,
        InstallScriptService $installScriptService
    ) {
        $this->alert = $alert;
        $this->installScriptService = $installScriptService;
        $this->repository = $repository;
    }

    /**
     * Handle requests to render installation script for an Egg.
     */
    public function index(int $egg): View
    {
        $egg = $this->repository->getWithCopyAttributes($egg);
        $copy = $this->repository->findWhere([
            ['copy_script_from', '=', null],
            ['nest_id', '=', $egg->nest_id],
            ['id', '!=', $egg],
        ]);

        $rely = $this->repository->findWhere([
            ['copy_script_from', '=', $egg->id],
        ]);

        return view('admin.eggs.scripts', [
            'copyFromOptions' => $copy,
            'relyOnScript' => $rely,
            'egg' => $egg,
        ]);
    }

    /**
     * Handle a request to update the installation script for an Egg.
     *
     * @throws \river\Exceptions\Model\DataValidationException
     * @throws \river\Exceptions\Repository\RecordNotFoundException
     * @throws \river\Exceptions\Service\Egg\InvalidCopyFromException
     */
    public function update(EggScriptFormRequest $request, Egg $egg): RedirectResponse
    {
        $this->installScriptService->handle($egg, $request->normalize());
        $this->alert->success(trans('admin/nests.eggs.notices.script_updated'))->flash();

        return redirect()->route('admin.nests.egg.scripts', $egg);
    }
}
