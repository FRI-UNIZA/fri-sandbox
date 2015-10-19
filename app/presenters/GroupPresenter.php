<?php

namespace App\Presenters;

use Nette,
    Grido\Grid;

class GroupPresenter extends BasePresenter
{
    /**
     * @inject
     * @var \App\Models\UserModel
     */
    public $userModel;

    /**
     * @inject
     * @var \App\Models\GroupModel
     */
    public $groupModel;

    protected function createComponentGrid($name)
    {
        $grid = new Grid($this, $name);
        $grid->setModel($this->groupModel->findAll());

        $grid->addColumnText('nazov', 'Názov')
            ->setSortable()
                ->setFilterText();
        
        $grid->addColumnText('pocet', 'Počet')
                ->setSortable();
    }
    
    public function renderList()
    {
        $this->template->groups = $this->groupModel->findAll();
    }
}
