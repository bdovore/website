<?php

class Browser extends Bdo_Controller
{
    private function text(): string
    {
        $value = $_GET['q'] ?? $_GET['let'] ?? $_POST['search'] ?? '';
        return is_string($value) ? mb_substr(trim($value), 0, 120) : '';
    }

    private function filters(): array
    {
        $this->loadModel('Genre');
        $rayons = [1 => 'BD', 2 => 'Mangas', 3 => 'Comics'];
        $origine = getValInteger('origine');
        $rayon = is_int($origine) ? ($rayons[$origine] ?? '') : '';
        $choices = array_filter($this->Genre->all()->a_dataQuery,
            static fn($genre) => !$rayon || $genre->ORIGINE === $rayon);
        $allowed = array_map('intval', array_column($choices, 'ID_GENRE'));
        $selected = array_slice(array_values(array_intersect($allowed, (array)getValInteger('a_idGenre', []))), 0, 10);
        return compact('rayons','origine','choices','selected') + ['ids' => $selected ?: ($rayon ? $allowed : [])];
    }

    public function Index()
    {
        $this->loadModel('BrowserCatalogue');
        $filters = $this->filters();
        $type = $_GET['rb_browse'] ?? 'ser';
        $type = is_string($type) && isset(BrowserCatalogue::TYPES[$type]) ? $type : 'ser';
        $this->view->addCssFile('style/browser.css');
        $this->view->set_var($filters + ['PAGETITLE'=>'Explorer le catalogue · BD Browser',
            'type'=>$type, 'query'=>$this->text(), 'types'=>BrowserCatalogue::TYPES]);
        $this->view->render();
    }

    public function Results()
    {
        $this->loadModel('BrowserCatalogue');
        $query = $this->text();
        $parent = $_GET['parent'] ?? '';
        $type = $_GET['type'] ?? 'ser';
        $children = ['ser'=>'alb', 'aut'=>'ser', 'edit'=>'col', 'col'=>'ser'];
        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
        $publisher = filter_input(INPUT_GET, 'publisher', FILTER_VALIDATE_INT);
        if (!is_string($parent) || !is_string($type) ||
            ($parent !== '' && (!isset($children[$parent]) || $children[$parent] !== $type ||
             $id === false || $id === null || $id < ($parent === 'col' ? 0 : 1) || ($parent === 'col' && $publisher < 1))) ||
            ($parent === '' && !isset(BrowserCatalogue::TYPES[$type]) && !($type === 'all' && $query !== ''))) {
            http_response_code(400);
            return;
        }
        $filtered = !empty($_GET['origine']) || !empty($_GET['a_idGenre']);
        $genres = ($filtered && $query === '' && !isset($_GET['global'])) ? $this->filters()['ids'] : [];
        $offset = max(0, (int)filter_input(INPUT_GET, 'offset', FILTER_VALIDATE_INT));
        $groups = [];
        foreach ($type === 'all' ? array_keys(BrowserCatalogue::TYPES) : [$type] as $kind) {
            $size = $query !== '' ? 8 : 24;
            $groups[$kind] = $this->BrowserCatalogue->page($kind, $parent ? '' : $query, $genres,
                $parent, (int)$id, (int)$publisher, $offset, $size);
        }
        $approximate = false;
        if ($query !== '' && $parent === '' && $offset === 0 && !array_filter(array_column($groups, 'rows'))) {
            foreach (array_keys($groups) as $kind) {
                $groups[$kind]['rows'] = $this->BrowserCatalogue->suggestions($kind, $query, $size);
                if ($groups[$kind]['rows']) $approximate = true;
            }
        }
        $this->view->layout = 'ajax';
        $this->view->set_var(compact('approximate','groups','type','parent','id','offset','size') + ['canEdit'=>User::minAccesslevel(1), 'mobile'=>($_GET['mobile'] ?? '') === 'T']);
        $this->view->render();
    }
}
