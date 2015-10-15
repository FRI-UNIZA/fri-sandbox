<?php

namespace App\Presenters;

use Nette;

class SignPresenter extends BasePresenter {
    
    /**
	 * @inject
	 * @var \App\Models\UserModel
	 */
	public $userModel;

    protected function createComponentSignInForm() {
        $form = new Nette\Application\UI\Form;
        $form->addText('meno', 'Prihlasovacii email:')
                ->setRequired('Prosím vyplnte email.');

        $form->addPassword('heslo', 'Heslo:')
                ->setRequired('Prosím vyplnte heslo.');

        $form->addCheckbox('remember', 'Zůstat přihlášen');

        $form->addSubmit('send', 'Přihlásit');

        $form->onSuccess[] = array($this, 'signInFormSucceeded');
        return $form;
    }

    protected function createComponentRegisterForm() {
        $form = new Nette\Application\UI\Form;
        $form->addText('meno', 'Prihlasovacii email:')
                ->setRequired('Prosím vyplnte email.');

        $form->addPassword('heslo', 'Heslo:')
                ->setRequired('Prosím vyplnte heslo.');

        $form->addCheckbox('remember', 'Zůstat přihlášen');

        $form->addSubmit('send', 'Přihlásit');

        $form->onSuccess[] = array($this, 'registerFormSucceeded');
        return $form;
    }

    public function signInFormSucceeded($form) {
        $values = $form->values;

        try {
            $this->getUser()->login($values->meno, $values->heslo);
            $this->redirect('Homepage:');
        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError('Nesprávné přihlašovací jméno nebo heslo.');
        }
    }

    public function registerFormSucceeded($form) {
        $values = $form->values;

        try {
            $this-> userModel ->save($values);
            $this->getUser()->login($values->meno, $values->heslo);
            $this->redirect('Homepage:');
        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError('Nesprávné přihlašovací jméno nebo heslo.');
        }
    }

}
