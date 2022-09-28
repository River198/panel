<?php

namespace river\Http\Controllers\Base;

use river\Http\Controllers\Controller;
use river\Contracts\Repository\ServerRepositoryInterface;

class IndexController extends Controller
{
    /**
     * @var \river\Contracts\Repository\ServerRepositoryInterface
     */
    protected $repository;

    /**
     * IndexController constructor.
     */
    public function __construct(ServerRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Returns listing of user's servers.
     *
     * @return \Illuminate\View\View
     */
    public function index()
    {
        return view('templates/base.core');
    }
}
