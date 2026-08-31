<?php

class Parabd extends Bdo_Controller
{
    private function service()
    {
        if (!isset($this->ParabdService)) $this->loadModel('ParabdService');
        return $this->ParabdService;
    }

    private function enabled()
    {
        if (!defined('BDO_PARABD_ENABLED') || !BDO_PARABD_ENABLED) {
            http_response_code(404);
            die('Fonctionnalité Para-BD indisponible.');
        }
        if (!User::minAccesslevel(defined('BDO_PARABD_MIN_LEVEL') ? BDO_PARABD_MIN_LEVEL : 1)) {
            $this->jsonError('AUTH_REQUIRED', 'Vous devez être authentifié pour accéder à Para-BD.', array(), 401);
            return false;
        }
        return true;
    }

    private function userId()
    {
        return isset($_SESSION['userConnect']->user_id) ? intval($_SESSION['userConnect']->user_id) : 0;
    }

    private function requireMutation($scope)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonError('VALIDATION_ERROR', 'Cette opération doit utiliser POST.', array(), 405);
            return false;
        }
        if (!$this->userId()) {
            $this->jsonError('AUTH_REQUIRED', 'Authentification requise.', array(), 401);
            return false;
        }
        $token = isset($_POST['csrf_token']) ? $_POST['csrf_token'] : (isset($_SERVER['HTTP_X_CSRF_TOKEN']) ? $_SERVER['HTTP_X_CSRF_TOKEN'] : '');
        if (!parabdValidateCsrf($scope, $token)) {
            $this->jsonError('CSRF_INVALID', 'Le formulaire a expiré. Rechargez la page.', array(), 403);
            return false;
        }
        return true;
    }

    private function renderJson($payload, $status = 200)
    {
        http_response_code($status);
        $this->view->layout = 'ajax';
        $this->view->addPhtmlFile('parabd/json', 'BODY');
        $this->view->set_var('json', json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        $this->view->render();
    }

    private function jsonOk($data = array(), $status = 200)
    {
        $this->renderJson(array('ok' => true, 'data' => $data), $status);
    }

    private function jsonError($code, $message, $fields = array(), $status = 400)
    {
        $this->renderJson(array('ok' => false, 'error' => array('code' => $code, 'message' => $message, 'fields' => (object) $fields)), $status);
    }

    private function handle(callable $callback)
    {
        try {
            $this->jsonOk($callback());
        } catch (ParabdException $error) {
            $statuses = array('AUTH_REQUIRED' => 401, 'CSRF_INVALID' => 403, 'NOT_FOUND' => 404, 'DUPLICATE_EXACT' => 409, 'REVISION_CONFLICT' => 409, 'RATE_LIMITED' => 429);
            $this->jsonError($error->errorCode, $error->getMessage(), $error->fields, isset($statuses[$error->errorCode]) ? $statuses[$error->errorCode] : 422);
        } catch (Throwable $error) {
            if (defined('DEBUG') && DEBUG) $this->jsonError('INTERNAL_ERROR', $error->getMessage(), array(), 500);
            else $this->jsonError('INTERNAL_ERROR', 'Une erreur technique est survenue.', array(), 500);
        }
    }

    public function Index()
    {
        if (!$this->enabled()) return;
        $this->view->addCssFile('style/parabd.css?v=20260815b');
        $this->view->addJavascriptFile('script/parabd.js');
        $search = getVal('q', '');
        $filterType = getVal('filter_type', '');
        $filterId = getValInteger('filter_id', 0);
        $filterValue = getVal('filter_value', '');
        $this->view->set_var(array(
            'PAGETITLE' => 'Catalogue Para-BD', 'ROBOTS' => 'noindex,nofollow',
            'items' => $this->service()->getCatalogue($search, $filterType, $filterId, $filterValue), 'search' => $search,
            'filter_type' => $filterType, 'filter_id' => $filterId, 'filter_value' => $filterValue,
            'explicit_allowed' => (bool) Bdo_Cfg::getVar('explicit'),
            'can_admin' => User::minAccesslevel(1)
        ));
        $this->view->render();
    }

    public function Fiche()
    {
        if (!$this->enabled()) return;
        $this->view->addCssFile('style/parabd.css?v=20260831d');
        $this->view->addJavascriptFile('script/parabd.js');
        $item = $this->service()->getItem(getValInteger('id', 0));
        if (!$item) { http_response_code(404); die('Objet Para-BD introuvable.'); }
        if (!empty($item['REDIRECT_ID'])) { header('Location: ' . BDO_URL . 'parabd/fiche?id=' . intval($item['REDIRECT_ID']), true, 301); return; }
        $userId = $this->userId();
        $canContribute = $userId && User::minAccesslevel(defined('BDO_PARABD_MIN_LEVEL') ? BDO_PARABD_MIN_LEVEL : 1);
        $revisionsToVote = array();
        $ownRevisions = array();
        foreach ($this->service()->getRevisionsForItem(intval($item['ID_ITEM'])) as $revision) {
            if ($userId && intval($revision['AUTHOR_ID']) === $userId) $ownRevisions[] = $revision;
            else $revisionsToVote[] = $revision;
        }
        $discussion = $canContribute ? $this->service()->getDiscussion(intval($item['ID_ITEM'])) : array('entries' => array(), 'comment_count' => 0);
        $this->view->set_var(array('PAGETITLE' => $item['TITLE'] . ' - Para-BD', 'ROBOTS' => 'noindex,nofollow', 'item' => $item,
            'copies' => $canContribute ? $this->service()->getUserCopies($userId) : array(), 'revisions_to_vote' => $revisionsToVote, 'own_revisions' => $ownRevisions,
            'discussion' => $discussion,
            'user_id' => $userId,
            'csrf_token' => $canContribute ? parabdCsrfToken('parabd-write') : '', 'can_contribute' => $canContribute,
            'trusted' => $canContribute ? $this->service()->isTrusted($userId) : false, 'charter_version' => BDO_PARABD_CHARTER_VERSION,
            'charter_accepted' => $canContribute ? $this->service()->hasAcceptedCharter($userId) : false,
            'explicit_allowed' => (bool) Bdo_Cfg::getVar('explicit'), 'can_admin' => $userId && User::minAccesslevel(1)));
        $this->view->render();
    }

    public function Autocomplete()
    {
        if (!$this->enabled()) return;
        $this->handle(function () { return array('suggestions' => $this->service()->autocompleteCatalogue(getVal('term', ''))); });
    }

    public function Fieldautocomplete()
    {
        if (!$this->enabled()) return;
        $this->handle(function () { return array('suggestions' => $this->service()->autocompleteField(getVal('field', ''), getVal('term', ''))); });
    }

    public function Charte()
    {
        if (!$this->enabled()) return;
        $this->view->addCssFile('style/parabd.css?v=20260813b');
        $this->view->set_var(array('PAGETITLE' => 'Charte de contribution Para-BD', 'ROBOTS' => 'noindex,nofollow', 'charter_version' => BDO_PARABD_CHARTER_VERSION));
        $this->view->render();
    }

    public function Search()
    {
        if (!$this->enabled()) return;
        $input = array(
            'TITLE' => getVal('title', getVal('q', '')), 'TYPE_ID' => getValInteger('type_id', 0), 'MANUFACTURER' => getVal('manufacturer', ''),
            'PUBLISHER' => getVal('publisher', ''),
            'RELEASE_DATE' => getVal('release_date', ''), 'WIDTH_MM' => getVal('width_mm', ''), 'HEIGHT_MM' => getVal('height_mm', ''), 'DEPTH_MM' => getVal('depth_mm', ''),
            'AUTHOR_ID' => getValInteger('author_id', 0), 'SERIES_ID' => getValInteger('series_id', 0), 'TOME_ID' => getValInteger('tome_id', 0)
        );
        $identifiers = json_decode(getVal('identifiers', '[]'), true);
        $input['identifiers'] = is_array($identifiers) ? $identifiers : array();
        $this->handle(function () use ($input) { return array('candidates' => $this->service()->searchDuplicates($input)); });
    }

    public function Create()
    {
        if (!$this->enabled()) return;
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->view->addCssFile('style/parabd.css?v=20260831c');
            $this->view->addJavascriptFile('script/parabd.js?v=20260831c');
            $this->view->set_var(array('PAGETITLE' => 'Créer un objet Para-BD', 'ROBOTS' => 'noindex,nofollow', 'types' => $this->service()->getTypes(),
                'csrf_token' => parabdCsrfToken('parabd-write'), 'charter_version' => BDO_PARABD_CHARTER_VERSION,
                'charter_accepted' => $this->service()->hasAcceptedCharter($this->userId())));
            $this->view->render();
            return;
        }
        if (!$this->requireMutation('parabd-write')) return;
        $this->handle(function () {
            if (!empty($_POST['accept_charter'])) $this->service()->acceptCharter($this->userId(), true);
            $result = $this->service()->createItem($this->userId(), $_POST, isset($_FILES['visual']) ? $_FILES['visual'] : null);
            $action = isset($_POST['collection_action']) ? $_POST['collection_action'] : 'none';
            $result['redirect_url'] = in_array($action, array('OWNED','WISHLIST'), true)
                ? BDO_URL . 'macollection/parabd?list=' . ($action === 'WISHLIST' ? 'wishlist' : 'owned') . '&created=' . intval($result['item_id'])
                : BDO_URL . 'parabd/fiche?id=' . intval($result['item_id']);
            return $result;
        });
    }

    public function Savecopy()
    {
        if (!$this->enabled() || !$this->requireMutation('parabd-write')) return;
        $this->handle(function () { return array('copy_id' => $this->service()->saveCopy($this->userId(), $_POST)); });
    }

    public function Removecopy()
    {
        if (!$this->enabled() || !$this->requireMutation('parabd-write')) return;
        $this->handle(function () { $this->service()->removeCopy($this->userId(), postValInteger('copy_id', 0)); return array(); });
    }

    public function Contribute()
    {
        if (!$this->enabled() || !$this->requireMutation('parabd-write')) return;
        $this->handle(function () {
            if (!empty($_POST['accept_charter'])) $this->service()->acceptCharter($this->userId(), true);
            return $this->service()->contribute($this->userId(), postValInteger('item_id', 0), postValInteger('base_revision_no', 0), strtoupper(postVal('field', '')), postVal('value', ''));
        });
    }

    public function Vote()
    {
        if (!$this->enabled() || !$this->requireMutation('parabd-write')) return;
        $this->handle(function () { return $this->service()->vote($this->userId(), postValInteger('revision_id', 0), postVal('vote', ''), postVal('reason', '')); });
    }

    public function Comment()
    {
        if (!$this->enabled() || !$this->requireMutation('parabd-write')) return;
        $this->handle(function () { return array('discussion_id' => $this->service()->addDiscussionComment($this->userId(), postValInteger('item_id', 0), postValInteger('revision_id', 0), postVal('body', ''))); });
    }

    public function Report()
    {
        if (!$this->enabled() || !$this->requireMutation('parabd-write')) return;
        $this->handle(function () { return array('report_id' => $this->service()->report($this->userId(), postVal('target_type', ''), postValInteger('target_id', 0), postVal('reason', ''), postVal('details', ''))); });
    }

    public function Media()
    {
        if (!$this->enabled() || !$this->requireMutation('parabd-write')) return;
        $this->handle(function () {
            if (!empty($_POST['accept_charter'])) $this->service()->acceptCharter($this->userId(), true);
            return array('media_id' => $this->service()->addMedia($this->userId(), postValInteger('item_id', 0), isset($_FILES['visual']) ? $_FILES['visual'] : null, postVal('media_type', 'GALLERY'), postVal('visual_url', ''), postValInteger('is_explicit', 0) === 1));
        });
    }
}
