<?php

namespace river\Http\Controllers\Admin;

use Illuminate\View\View;
use river\Http\Controllers\Controller;
use river\Services\Helpers\SoftwareVersionService;

class BaseController extends Controller
{
    /**
     * @var \river\Services\Helpers\SoftwareVersionService
     */
    private $version;

    /**
     * BaseController constructor.
     */
    public function __construct(SoftwareVersionService $version)
    {
        $this->version = $version;
    }

    /**
     * Return the admin index view.
     */
    public function index(): View
    {
        return view('admin.index', ['version' => $this->version]);
    }
}
