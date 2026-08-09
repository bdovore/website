<?php

class Adminparabd extends Bdo_Controller
{
    private function service()
    {
        if (!isset($this->ParabdService)) $this->loadModel('ParabdService');
        return $this->ParabdService;
    }

    private function guard($mutation = false)
    {
        if (!defined('BDO_PARABD_ENABLED') || !BDO_PARABD_ENABLED) { http_response_code(404); die('Fonctionnalité Para-BD indisponible.'); }
        if (!User::minAccesslevel(1)) { http_response_code(403); die('Accès refusé.'); }
        if ($mutation && ($_SERVER['REQUEST_METHOD'] !== 'POST' || !parabdValidateCsrf('parabd-admin', postVal('csrf_token', '')))) {
            http_response_code($_SERVER['REQUEST_METHOD'] === 'POST' ? 403 : 405); die('Requête Para-BD invalide.');
        }
        return true;
    }

    private function mutate(callable $callback)
    {
        $this->guard(true);
        try {
            $callback();
            header('Location: ' . BDO_URL . 'adminparabd');
        } catch (Throwable $error) {
            http_response_code($error instanceof ParabdException && $error->errorCode === 'NOT_FOUND' ? 404 : 422);
            die(htmlspecialchars($error->getMessage(), ENT_QUOTES, 'UTF-8'));
        }
    }

    public function Index()
    {
        $this->guard();
        $this->view->addCssFile('style/parabd.css');
        $this->view->set_var(array('PAGETITLE' => 'Administration Para-BD', 'ROBOTS' => 'noindex,nofollow', 'queues' => $this->service()->adminQueues(), 'csrf_token' => parabdCsrfToken('parabd-admin')));
        $this->view->render();
    }

    public function Merge()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->guard();
            $source = $this->service()->getItem(getValInteger('source_id', 0), true);
            $target = $this->service()->getItem(getValInteger('target_id', 0), true);
            if (!$source || !$target) { http_response_code(404); die('Fiche à fusionner introuvable.'); }
            $this->view->addCssFile('style/parabd.css');
            $this->view->set_var(array('PAGETITLE' => 'Fusion Para-BD', 'ROBOTS' => 'noindex,nofollow', 'source' => $source, 'target' => $target, 'csrf_token' => parabdCsrfToken('parabd-admin')));
            $this->view->render();
            return;
        }
        $this->mutate(function () {
            $sourceId = postValInteger('source_id', 0); $targetId = postValInteger('target_id', 0);
            $source = $this->service()->getItem($sourceId, true); $target = $this->service()->getItem($targetId, true);
            if (!$source || !$target) throw new ParabdException('NOT_FOUND', 'Fiche à fusionner introuvable.');
            $choices = postVal('prefer', array()); $fields = array();
            if (is_array($choices)) foreach ($choices as $field => $choice) {
                if ($choice === 'source' && array_key_exists($field, $source)) $fields[$field] = $source[$field];
                elseif ($choice === 'target' && array_key_exists($field, $target)) $fields[$field] = $target[$field];
            }
            $this->service()->merge(intval($_SESSION['userConnect']->user_id), $sourceId, $targetId, $fields, postValInteger('primary_media_id', 0));
        });
    }

    public function Revision()
    {
        $this->mutate(function () { $this->service()->resolveRevision(intval($_SESSION['userConnect']->user_id), postValInteger('revision_id', 0), postVal('decision', '') === 'accept'); });
    }

    public function Report()
    {
        $this->mutate(function () { $this->service()->resolveReport(intval($_SESSION['userConnect']->user_id), postValInteger('report_id', 0), postVal('decision', '') === 'dismiss' ? 'DISMISSED' : 'RESOLVED'); });
    }

    public function Duplicate()
    {
        $this->mutate(function () { $this->service()->resolveDuplicate(intval($_SESSION['userConnect']->user_id), postValInteger('duplicate_id', 0), postVal('decision', '') === 'collision' ? 'COLLISION' : 'IGNORED'); });
    }

    public function Visibility()
    {
        $this->mutate(function () { $this->service()->moderateItem(intval($_SESSION['userConnect']->user_id), postValInteger('item_id', 0), postVal('status', '') === 'ACTIVE' ? 'ACTIVE' : 'HIDDEN'); });
    }
}
