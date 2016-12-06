<?php

namespace App\Routing;

use App\Presenters\PresenterContract;
use Illuminate\Support\Collection;

/**
 * Interface RouteContract
 * @package App\Routing
 */
interface RouteContract
{
    /**
     * @return string
     */
    public function getId();

    /**
     * @return string
     */
    public function getMethod();

    /**
     * @return string
     */
    public function getPath();

    /**
     * @return string
     */
    public function getFormat();

    /**
     * @return bool
     */
    public function isPublic();

    /**
     * @return bool
     */
    public function isAggregate();

    /**
     * @return Collection
     */
    public function getActions();

    /**
     * @return PresenterContract
     */
    public function getPresenter();

    /**
     * @return array
     */
    public function getConfig();

    /**
     * @param ActionContract $action
     * @return $this
     */
    public function addAction(ActionContract $action);
}