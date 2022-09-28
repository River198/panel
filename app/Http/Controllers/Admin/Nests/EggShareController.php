<?php
/**
 * river - Panel
 * Copyright (c) 2015 - 2017 Dane Everitt <dane@daneeveritt.com>.
 *
 * This software is licensed under the terms of the MIT license.
 * https://opensource.org/licenses/MIT
 */

namespace river\Http\Controllers\Admin\Nests;

use river\Models\Egg;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use river\Http\Controllers\Controller;
use Symfony\Component\HttpFoundation\Response;
use river\Services\Eggs\Sharing\EggExporterService;
use river\Services\Eggs\Sharing\EggImporterService;
use river\Http\Requests\Admin\Egg\EggImportFormRequest;
use river\Services\Eggs\Sharing\EggUpdateImporterService;

class EggShareController extends Controller
{
    /**
     * @var \Prologue\Alerts\AlertsMessageBag
     */
    protected $alert;

    /**
     * @var \river\Services\Eggs\Sharing\EggExporterService
     */
    protected $exporterService;

    /**
     * @var \river\Services\Eggs\Sharing\EggImporterService
     */
    protected $importerService;

    /**
     * @var \river\Services\Eggs\Sharing\EggUpdateImporterService
     */
    protected $updateImporterService;

    /**
     * OptionShareController constructor.
     */
    public function __construct(
        AlertsMessageBag $alert,
        EggExporterService $exporterService,
        EggImporterService $importerService,
        EggUpdateImporterService $updateImporterService
    ) {
        $this->alert = $alert;
        $this->exporterService = $exporterService;
        $this->importerService = $importerService;
        $this->updateImporterService = $updateImporterService;
    }

    /**
     * @throws \river\Exceptions\Repository\RecordNotFoundException
     */
    public function export(Egg $egg): Response
    {
        $filename = trim(preg_replace('/[^\w]/', '-', kebab_case($egg->name)), '-');

        return response($this->exporterService->handle($egg->id), 200, [
            'Content-Transfer-Encoding' => 'binary',
            'Content-Description' => 'File Transfer',
            'Content-Disposition' => 'attachment; filename=egg-' . $filename . '.json',
            'Content-Type' => 'application/json',
        ]);
    }

    /**
     * Import a new service option using an XML file.
     *
     * @throws \river\Exceptions\Model\DataValidationException
     * @throws \river\Exceptions\Repository\RecordNotFoundException
     * @throws \river\Exceptions\Service\Egg\BadJsonFormatException
     * @throws \river\Exceptions\Service\InvalidFileUploadException
     */
    public function import(EggImportFormRequest $request): RedirectResponse
    {
        $egg = $this->importerService->handle($request->file('import_file'), $request->input('import_to_nest'));
        $this->alert->success(trans('admin/nests.eggs.notices.imported'))->flash();

        return redirect()->route('admin.nests.egg.view', ['egg' => $egg->id]);
    }

    /**
     * Update an existing Egg using a new imported file.
     *
     * @throws \river\Exceptions\Model\DataValidationException
     * @throws \river\Exceptions\Repository\RecordNotFoundException
     * @throws \river\Exceptions\Service\Egg\BadJsonFormatException
     * @throws \river\Exceptions\Service\InvalidFileUploadException
     */
    public function update(EggImportFormRequest $request, Egg $egg): RedirectResponse
    {
        $this->updateImporterService->handle($egg, $request->file('import_file'));
        $this->alert->success(trans('admin/nests.eggs.notices.updated_via_import'))->flash();

        return redirect()->route('admin.nests.egg.view', ['egg' => $egg]);
    }
}
