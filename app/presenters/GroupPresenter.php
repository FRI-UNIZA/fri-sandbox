<?php

namespace App\Presenters;

use Nette,
    Grido\Grid;
use Nette\Application\UI\Form;
use Tracy\Debugger;

class GroupPresenter extends BasePresenter
{
    /**
     * @inject
     * @var \App\Models\GroupModel
     */
    public $groupModel;

    protected function createComponentGrid($name)
    {
        $grid = new Grid($this, $name);
        $grid->setModel($this->groupModel->findAll());

        $grid->addColumnText('name', 'Názov')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('desc','Popis')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnNumber('pocet','Počet ľudí')
            ->setSortable();

        $grid->filterRenderType = $this->filterRenderType;

        $grid->addActionHref('join', 'Pridať sa')
            ->setIcon('plus');
        $grid->addActionHref('leave', 'Odísť')
            ->setIcon('minus')
            ->setConfirm(function($item) {
                return "Are you sure you want to delete {$item->name}";
            });
        $grid->addActionHref('listUsers', 'Zoznam účastníkov')
            ->setIcon('list');

    }

    public function actionJoin($id) {
        if($this->isLoggedIn) {
            if($this->userModel->addUserToGroup($this->getUser()->getId(),$id)) {
                $this->flashMessage('Pridal si sa do skupiny.');
            }
            else {
                $this->flashMessage('V tejto skupine sa už nachádzaš.');
            }
        }
        else {
            $this->flashMessage('Najprv sa prihlás!');
        }

        $this->redirect('Group:list');
    }

    public function actionleave($id) {
        if($this->isLoggedIn) {
            if($this->userModel->removeUserFromGroup($this->getUser()->getId(),$id)) {
                $this->flashMessage('Odišiel si zo do skupiny.');
            }
            else {
                $this->flashMessage('Nemôžeš odísť zo skupiny, v ktorej sa nenachádzaš.');
            }
        }
        else {
            $this->flashMessage('Najprv sa prihlás!');
        }

        $this->redirect('Group:list');
    }

    public function actionListUsers($id) {
        $this->redirect('Group:users',array('groupId' => $id));
    }


    public function renderList()
    {
        $this->template->groups = $this->groupModel->findAll();
    }

    public function renderUsers($groupId) {
        $this->template->groupName = $this->groupModel->getName($groupId);
    }

    public function createComponentUsersGrid($name) {

        $groupId = $this->getParameter('groupId');

        $grid = new Grid($this, $name);

        $grid->setModel($this->userModel->findAll($groupId));

        $grid->addColumnText('username', 'Užívateľ')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('email','Email-ová adresa')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnNumber('last_login','Posledné prihlásenie')
            ->setSortable();

        $grid->filterRenderType = $this->filterRenderType;

        $grid->hasActions(false);
    }

    public function createComponentAddGroupForm() {
        $form = new Form();

        $form->addText('name', 'Názov skupiny')
            ->setAttribute('placeholder','Názov skupiny..');

        $form->addTextArea('desc','Popis skupiny',null,3)
            ->setValue('Moja nová skupina !');

        $form->addSubmit('add', 'Pridať');

        $form->onSuccess[] = [$this, 'addGroupFormSubmitted'];

        return $form;
    }

    public function addGroupFormSubmitted(Form $form)
    {
        $query = $form->getValues();

        $this->groupModel->addGroup($query);
        $this->flashMessage('Pridal si skupinu s názvom '.$query->name.'.');
        $this->redirect('Group:list');
    }

    public function handleOperations($operation, $id)
    {
        if ($id) {
            $row = implode(', ', $id);
            $this->flashMessage("Process operation '$operation' for row with id: $row...", 'info');
        } else {
            $this->flashMessage('No rows selected.', 'error');
        }
        if ($this->isAjax()) {
            isset($this['grid']) && $this['grid']->reload();
            $this->redrawControl('flashes');
        } else {
            $this->redirect($operation, array('id' => $id));
        }
    }
}