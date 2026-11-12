<?php

require_once dirname(__DIR__, 2) . '/library/Bdo/AccountApiController.php';

class Apicompte extends Bdo_AccountApiController
{
    public function Me() { $this->run('GET', function ($service, $input, $context) { return $service->profile($context['user']); }, true); }
    public function Update() { $this->run('POST', function ($service, $input, $context) { return $service->updateProfile($context['user'], $input); }, true); }
    public function Requestemail() { $this->run('POST', function ($service, $input, $context) { return $service->requestEmail($context['user'], $input); }, true); }
    public function Confirmemail() { $this->run('POST', function ($service, $input, $context) { return $service->confirmEmail($context['user'], $input); }, true); }
    public function Password() {
        $this->run('POST', function ($service, $input, $context) {
            $service->changePassword($context['user'], $input);
            return array('message' => 'Mot de passe modifié. Veuillez vous reconnecter.');
        }, true);
    }
    public function Delete() {
        $this->run('POST', function ($service, $input, $context) {
            $service->prepareDeletion($context['user'], $input);
            User::deleteAllDataForUser(intval($context['user']->user_id));
            return array('deleted' => true);
        }, true);
    }
}
