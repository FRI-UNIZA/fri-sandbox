<?php

namespace App\Presenters;

use Grido\Grid;
use Nette;
use Nette\Application\UI\Form;
use Tracy\Debugger;


class HomepagePresenter extends BasePresenter {

    /**
     * @inject
     * @var \App\Models\GroupModel
     */
    public $groupModel;

    /**
     * @var Array
     * @persistent
     */
    public $groupsOutput = array();

    /**
     * @var string
     * @persistent
     */
    public $searchInput = null;

    public function renderDefault() {
        $this->template->groupsOutput = $this->groupsOutput;
        Debugger::barDump('aaa');
    }

    public function actionReset() {
        $this->groupsOutput = array();
        $this->searchInput = '';
        $this->redirect('Homepage:default');
    }

    public function createComponentSearchForGroupForm()
    {
        $form = new Form();

        $form->addText('name', 'Hľadaj skupinu')
            ->setAttribute('placeholder','Názov skupiny..')
            ->setValue($this->searchInput == null ? null : $this->searchInput);

        $form->addSubmit('search', 'Hľadať');

        $form->onSuccess[] = [$this, 'searchForGroupFormSubmitted'];

        return $form;
    }

    public function searchForGroupFormSubmitted(Form $form)
    {
        $query = $form->getValues();

        $this->groupsOutput = $this->groupModel->findAllByName($query->name);
        $this->searchInput = $query->name;

        $this->template->groupsOutput = ($this->groupsOutput == null);
    }

    protected function createComponentSearchGrid($name)
    {
        if($this->groupsOutput) {
            $grid = new Grid($this, $name);
            $grid->setModel($this->groupsOutput);

            $grid->setPrimaryKey('id');

            $grid->addColumnText('name', 'Názov')
                ->setSortable();

            $grid->addColumnText('desc','Popis')
                ->setSortable();

            $grid->addColumnNumber('pocet','Počet ľudí')
                ->setSortable();

//            $operation = array('join' => 'Pripojiť sa', 'leave' => 'Odísť');
//            $grid->setOperation($operation, $this->handleOperations)
//                ->setConfirm('delete', 'Are you sure you want to delete %i items?');

            $grid->filterRenderType = $this->filterRenderType;
        }
    }
}