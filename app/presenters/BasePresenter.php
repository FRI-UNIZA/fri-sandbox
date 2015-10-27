<?php

namespace App\Presenters;

use Nette;
use Tracy\Debugger;


/**
 * Base presenter for all application presenters.
 */
abstract class BasePresenter extends Nette\Application\UI\Presenter {

    /** @var string @persistent */
    public $filterRenderType = \Grido\Components\Filters\Filter::RENDER_INNER;

    /**
     * @inject
     * @var \App\Models\UserModel
     */
    public $userModel;

    public $isLoggedIn = false;

    public function startup() {
        parent::startup();
        $this->registerGlobalVariables();
        $this->registerTemplateVariables();
    }



    private function registerGlobalVariables() {
        Debugger::barDump('Lognuty: '.($this->getUser()->isLoggedIn()? 'true'.', id: '. $this->getUser()->getId() : 'false'));
        $this->isLoggedIn = $this->getUser()->isLoggedIn();
    }

    private function registerTemplateVariables() {


        $this->template->userName = $this->isLoggedIn ? $this->getUser()->getIdentity()->username : '';


        $this->template->isLoggedIn = $this->getUser()->isLoggedIn();
    }
}
