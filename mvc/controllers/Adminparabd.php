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

    private function mutate(callable $callback, $redirect = 'adminparabd')
    {
        $this->guard(true);
        try {
            $callback();
            header('Location: ' . BDO_URL . $redirect);
        } catch (Throwable $error) {
            http_response_code($error instanceof ParabdException && $error->errorCode === 'NOT_FOUND' ? 404 : 422);
            die(htmlspecialchars($error->getMessage(), ENT_QUOTES, 'UTF-8'));
        }
    }

    private function adminId()
    {
        return intval($_SESSION['userConnect']->user_id);
    }

    private function renderEditor($item, $mode, $error = '', $mediaError = '')
    {
        $history = $item ? $this->service()->getAdminItemHistory(intval($item['ID_ITEM'])) : array();
        $pendingRevisions = array_values(array_filter($history, function ($revision) {
            return in_array($revision['STATUS'], array('PENDING', 'CONFLICT'), true) && $revision['CHANGE_KIND'] !== 'CREATE';
        }));
        $this->view->addPhtmlFile('adminparabd/edit', 'BODY');
        $this->view->addCssFile('style/parabd.css?v=20260813b');
        $this->view->addJavascriptFile('script/parabd-admin.js');
        $itemId = $item ? intval($item['ID_ITEM']) : 0;
        $reportId = getValInteger('report_id', postValInteger('report_id', 0));
        $this->view->set_var(array(
            'PAGETITLE' => $mode === 'create' ? 'Créer une fiche Para-BD' : 'Modifier la fiche Para-BD #' . intval($item['ID_ITEM']),
            'ROBOTS' => 'noindex,nofollow', 'types' => $this->service()->getTypes(), 'item' => $item, 'mode' => $mode,
            'history' => $history, 'pending_revisions' => $pendingRevisions,
            'discussion' => $item ? $this->service()->getDiscussion($itemId, true) : array('entries' => array(), 'comment_count' => 0),
            'report' => $item ? $this->service()->getOpenReportForItem($reportId, $itemId) : null,
            'report_id' => $reportId,
            'csrf_token' => parabdCsrfToken('parabd-admin'), 'form_error' => $error, 'media_error' => $mediaError,
            'saved' => getValInteger('saved', 0), 'media_saved' => getValInteger('media_saved', 0),
            'revision_decision' => getVal('revision_decision', '')
        ));
        $this->view->render();
    }

    public function Index()
    {
        $this->guard();
        $this->view->addCssFile('style/parabd.css?v=20260813b');
        $search = getVal('q', ''); $status = getVal('status', '');
        $this->view->set_var(array('PAGETITLE' => 'Fiches Para-BD — Administration', 'ROBOTS' => 'noindex,nofollow',
            'items' => $this->service()->getAdminCatalogue($search, $status), 'search' => $search, 'status' => $status));
        $this->view->render();
    }

    public function Queues()
    {
        $this->guard();
        $this->view->addCssFile('style/parabd.css?v=20260813b');
        $this->view->set_var(array('PAGETITLE' => 'À traiter — Para-BD', 'ROBOTS' => 'noindex,nofollow', 'queues' => $this->service()->adminQueues(), 'csrf_token' => parabdCsrfToken('parabd-admin')));
        $this->view->render();
    }

    public function Create()
    {
        $this->guard($_SERVER['REQUEST_METHOD'] === 'POST');
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->renderEditor(null, 'create'); return; }
        try {
            $result = $this->service()->adminCreateItem($this->adminId(), $_POST, isset($_FILES['visual']) ? $_FILES['visual'] : null);
            header('Location: ' . BDO_URL . 'adminparabd/edit?id=' . intval($result['item_id']) . '&saved=1');
        } catch (Throwable $error) {
            http_response_code($error instanceof ParabdException && $error->errorCode === 'NOT_FOUND' ? 404 : 422);
            $this->renderEditor(null, 'create', $error->getMessage());
        }
    }

    public function Edit()
    {
        $this->guard($_SERVER['REQUEST_METHOD'] === 'POST');
        $itemId = $_SERVER['REQUEST_METHOD'] === 'POST' ? postValInteger('item_id', 0) : getValInteger('id', 0);
        $item = $this->service()->getAdminItem($itemId);
        if (!$item) { http_response_code(404); die('Objet Para-BD introuvable.'); }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->renderEditor($item, 'edit'); return; }
        try {
            $this->service()->adminUpdateItem($this->adminId(), $itemId, $_POST);
            header('Location: ' . BDO_URL . 'adminparabd/edit?id=' . $itemId . '&saved=1' . ($this->reportQuery() ?: ''));
        } catch (Throwable $error) {
            http_response_code($error instanceof ParabdException && $error->errorCode === 'NOT_FOUND' ? 404 : 422);
            $this->renderEditor($this->service()->getAdminItem($itemId), 'edit', $error->getMessage());
        }
    }

    public function Media()
    {
        $this->guard(true);
        $itemId = postValInteger('item_id', 0);
        $item = $this->service()->getAdminItem($itemId);
        if (!$item) { http_response_code(404); die('Objet Para-BD introuvable.'); }
        try {
            $this->service()->adminAddMedia($this->adminId(), $itemId, $_POST, isset($_FILES['visual']) ? $_FILES['visual'] : null);
            header('Location: ' . BDO_URL . 'adminparabd/edit?id=' . $itemId . '&media_saved=1' . ($this->reportQuery() ?: ''));
        } catch (Throwable $error) {
            http_response_code($error instanceof ParabdException && $error->errorCode === 'NOT_FOUND' ? 404 : 422);
            $this->renderEditor($this->service()->getAdminItem($itemId), 'edit', '', $error->getMessage());
        }
    }

    public function Merge()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->guard();
            $source = $this->service()->getItem(getValInteger('source_id', 0), true);
            $target = $this->service()->getItem(getValInteger('target_id', 0), true);
            if (!$source || !$target) { http_response_code(404); die('Fiche à fusionner introuvable.'); }
            $this->view->addCssFile('style/parabd.css?v=20260813b');
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
        $this->guard(true);
        $decision = postVal('decision', '');
        if (!in_array($decision, array('accept', 'reject'), true)) { http_response_code(422); die('Décision de validation invalide.'); }
        try {
            $result = $this->service()->resolveRevision($this->adminId(), postValInteger('revision_id', 0), $decision === 'accept');
            header('Location: ' . BDO_URL . 'adminparabd/edit?id=' . intval($result['item_id']) . '&revision_decision=' . ($decision === 'accept' ? 'accepted' : 'rejected'));
        } catch (Throwable $error) {
            http_response_code($error instanceof ParabdException && $error->errorCode === 'NOT_FOUND' ? 404 : 422);
            die(htmlspecialchars($error->getMessage(), ENT_QUOTES, 'UTF-8'));
        }
    }

    public function Report()
    {
        $this->guard(true);
        try {
            $result = $this->service()->resolveReport($this->adminId(), postValInteger('report_id', 0), 'RESOLVED');
            header('Location: ' . BDO_URL . 'adminparabd/edit?id=' . intval($result['item_id']) . '&report_resolved=1');
        } catch (Throwable $error) { http_response_code($error instanceof ParabdException && $error->errorCode === 'NOT_FOUND' ? 404 : 422); die(htmlspecialchars($error->getMessage(), ENT_QUOTES, 'UTF-8')); }
    }

    public function Duplicate()
    {
        $this->mutate(function () { $this->service()->resolveDuplicate(intval($_SESSION['userConnect']->user_id), postValInteger('duplicate_id', 0), 'IGNORED'); }, 'adminparabd/queues#doublons-potentiels');
    }

    public function Visibility()
    {
        $this->guard(true);
        try {
            $result = $this->service()->moderateItem($this->adminId(), postValInteger('item_id', 0), postVal('status', '') === 'ACTIVE' ? 'ACTIVE' : 'HIDDEN');
            header('Location: ' . BDO_URL . 'adminparabd/edit?id=' . intval($result['item_id']) . '&visibility=' . strtolower($result['status']));
        } catch (Throwable $error) { http_response_code($error instanceof ParabdException && $error->errorCode === 'NOT_FOUND' ? 404 : 422); die(htmlspecialchars($error->getMessage(), ENT_QUOTES, 'UTF-8')); }
    }

    public function Comment()
    {
        $this->guard(true);
        try {
            $itemId = postValInteger('item_id', 0);
            $this->service()->addDiscussionComment($this->adminId(), $itemId, postValInteger('revision_id', 0), postVal('body', ''), true);
            header('Location: ' . BDO_URL . 'adminparabd/edit?id=' . $itemId . '#discussion');
        } catch (Throwable $error) { http_response_code($error instanceof ParabdException && $error->errorCode === 'NOT_FOUND' ? 404 : 422); die(htmlspecialchars($error->getMessage(), ENT_QUOTES, 'UTF-8')); }
    }

    public function Discussion()
    {
        $this->guard(true);
        try {
            $itemId = postValInteger('item_id', 0);
            $this->service()->hideDiscussionComment($this->adminId(), postValInteger('discussion_id', 0));
            header('Location: ' . BDO_URL . 'adminparabd/edit?id=' . $itemId . '#discussion');
        } catch (Throwable $error) { http_response_code($error instanceof ParabdException && $error->errorCode === 'NOT_FOUND' ? 404 : 422); die(htmlspecialchars($error->getMessage(), ENT_QUOTES, 'UTF-8')); }
    }

    private function reportQuery()
    {
        $reportId = postValInteger('report_id', 0);
        return $reportId ? '&report_id=' . $reportId : '';
    }
}
