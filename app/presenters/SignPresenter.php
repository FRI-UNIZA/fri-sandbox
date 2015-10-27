<?php
/**
 * Created by PhpStorm.
 * User: LukeG
 * Date: 13/10/15
 * Time: 19:02
*/
namespace App\Presenters;

use Nette;

class SignPresenter extends BasePresenter {

    protected function createComponentRegisterForm() {

        $form = new Nette\Application\UI\Form;
        $form->addText('username', 'Uživateľské méno:')
            ->setRequired('Prosím, vyplňte svoje užívateľské méno.');

        $form->addText('email','Užívateľský e-mail:')
            ->addRule(Nette\Application\UI\Form::EMAIL,'Zadajte platný email.');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím, vyplňte svoje heslo.');

        $form->addCheckbox('remember', 'Zostať prihlásený');

        $form->addSubmit('send', 'Registrovať');

        $form->onSuccess[] = array($this, 'registerFormSucceeded');
        return $form;
    }

    protected function createComponentSignInForm() {

        $form = new Nette\Application\UI\Form;
        $form->addText('username', 'Uživateľské méno:')
            ->setRequired('Prosím, vyplňte svoje užívateľské méno.');

        $form->addPassword('password', 'Heslo:')
            ->setRequired('Prosím, vyplňte svoje heslo.');

        $form->addCheckbox('remember', 'Zostať prihlásený');

        $form->addSubmit('send', 'Prihlásiť');

        $form->onSuccess[] = array($this, 'signInFormSucceeded');
        return $form;
    }

    public function signInFormSucceeded($form)
    {
        $values = $form->values;

        try {
            $this->getUser()->login($values->username, $values->password);
            $this->redirect('Homepage:');

        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError('Nesprávné přihlašovací jméno nebo heslo.');
        }
    }

    public function registerFormSucceeded($form)
    {
        $values = $form->values;

        try {
            $this->userModel->save($values);
            $this->getUser()->login($values->username, $values->password);
            $this->redirect('Homepage:');

        } catch (Nette\Security\AuthenticationException $e) {
            $form->addError('Nesprávné přihlašovací jméno nebo heslo.');
        }
    }

    public function actionOut() {
        $this->getUser()->logout();
        $this->redirect('Homepage:');
    }

    public function actionWhat() {
        var_dump($this->getUser()->isLoggedIn());exit;
    }
}