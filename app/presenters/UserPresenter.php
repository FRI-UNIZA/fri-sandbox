<?php

namespace App\Presenters;

use Nette,
    Grido\Grid;
use Tracy\Debugger;

class UserPresenter extends BasePresenter {

    protected function createComponentGrid($name) {
        $grid = new Grid();
        $grid->setModel($this->userModel->findAll());

        $grid->setPrimaryKey('id');

        $grid->addColumnText('username','Používateľ')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnText('email','E-mail')
            ->setSortable()
            ->setFilterText();

        $grid->addColumnDate('last_login','Posledné prihlásenie')
            ->setSortable()
            ->setFilterDate();

        $grid->filterRenderType = $this->filterRenderType;

        $grid->addActionHref('remove', 'Odstrániť užívateľa')
            ->setIcon('minus')
            ->setConfirm(function($item) {
                return "Ste si istý, že chcete odstrániť užívateľa {$item->username}";
            });

        return $grid;
    }

    public function actionRemove($id) {
        if($this->isLoggedIn && ($this->getUser()->getId() == $id)) {
            $this->flashMessage('Nemôžte vymazať svoj účet, zatiaľ čo ste prihlásený.');
        }
        elseif($this->isLoggedIn && ($this->getUser()->getId() != $id)) {
            $this->userModel->removeUser($id);
            $this->flashMessage('Úspešne ste vymazali užívateľa s id: '.$id.'.');
        }
        elseif(!$this->isLoggedIn) {
            $this->flashMessage('Najprv sa prihláste.');
        }
        $this->redirect('User:list');
    }
}