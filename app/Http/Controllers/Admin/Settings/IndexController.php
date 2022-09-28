<?php

namespace river\Http\Controllers\Admin\Settings;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Prologue\Alerts\AlertsMessageBag;
use Illuminate\Contracts\Console\Kernel;
use river\Http\Controllers\Controller;
use river\Traits\Helpers\AvailableLanguages;
use river\Services\Helpers\SoftwareVersionService;
use river\Contracts\Repository\SettingsRepositoryInterface;
use river\Http\Requests\Admin\Settings\BaseSettingsFormRequest;

class IndexController extends Controller
{
    use AvailableLanguages;

    /**
     * @var \Prologue\Alerts\AlertsMessageBag
     */
    private $alert;

    /**
     * @var \Illuminate\Contracts\Console\Kernel
     */
    private $kernel;

    /**
     * @var \river\Contracts\Repository\SettingsRepositoryInterface
     */
    private $settings;

    /**
     * @var \river\Services\Helpers\SoftwareVersionService
     */
    private $versionService;

    /**
     * IndexController constructor.
     */
    public function __construct(
        AlertsMessageBag $alert,
        Kernel $kernel,
        SettingsRepositoryInterface $settings,
        SoftwareVersionService $versionService
    ) {
        $this->alert = $alert;
        $this->kernel = $kernel;
        $this->settings = $settings;
        $this->versionService = $versionService;
    }

    /**
     * Render the UI for basic Panel settings.
     */
    public function index(): View
    {
        return view('admin.settings.index', [
            'version' => $this->versionService,
            'languages' => $this->getAvailableLanguages(true),
        ]);
    }

    /**
     * Handle settings update.
     *
     * @throws \river\Exceptions\Model\DataValidationException
     * @throws \river\Exceptions\Repository\RecordNotFoundException
     */
    public function update(BaseSettingsFormRequest $request): RedirectResponse
    {
        foreach ($request->normalize() as $key => $value) {
            $this->settings->set('settings::' . $key, $value);
        }

        $this->kernel->call('queue:restart');
        $this->alert->success('Panel settings have been updated successfully and the queue worker was restarted to apply these changes.')->flash();

        return redirect()->route('admin.settings');
    }
}
