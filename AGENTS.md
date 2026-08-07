# BDovore Agent Notes

BDovore is a legacy PHP application using a custom MVC stack, not a standard framework. Prefer existing local patterns over introducing framework-style abstractions.

For a functional map of `mvc/`, see `docs/mvc-cartographie.md`.

## Project Shape

- Framework code lives in `library/Bdo/`.
- Controllers live in `mvc/controllers/` and usually extend `Bdo_Controller`.
- Models live in `mvc/models/` and usually extend `Bdo_Db_Line`.
- Controller views live in `mvc/views/views_controllers/<controller>/<action>.phtml`.
- Layouts live in `mvc/views/layout/`; the main layout is `default.phtml`, AJAX/JSON responses often use `ajax.phtml`.
- Frontend assets live under `public/`, especially `public/script/`, `public/style/`, and `public/jquery/`.
- Shared legacy helpers live under `inc/`.

## Routing And Controllers

Routing is initialized in `inc/conf.inc.php`: URL path parts are normalized to a controller and action with `ucfirst(strtolower(...))`. The default controller/action are `Accueil` and `Index`.

Action method names are not consistently suffixed with `Action`. Existing controllers commonly use methods such as `Index`, `majCollection`, `addSerie`, etc. Match the surrounding controller style instead of assuming `[action]Action()`.

`Bdo_Controller` creates `$this->view` and pre-registers the default body template:

```php
$this->view->addPhtmlFile(Bdo_Cfg::getVar('controller') . DS . Bdo_Cfg::getVar('action'), 'BODY');
```

Load models with:

```php
$this->loadModel('Useralbum');
$this->Useralbum->someMethod();
```

## Views And Rendering

The preferred modern rendering path in this codebase is usually:

```php
$this->view->set_var('name', $value);
$this->view->set_var(array('a' => $a, 'b' => $b));
$this->view->render();
```

Templates access values as `$view->name` or variables depending on the rendering path. Check nearby templates before changing data passing style.

For JSON/AJAX responses, existing code commonly sets:

```php
$this->view->layout = 'ajax';
$this->view->set_var('json', json_encode($payload));
$this->view->render();
```

The `ajax.phtml` layout echoes `$view->BODY`, so JSON actions typically need a matching view template that echoes `$view->json`.

## Models And Database

Models commonly use `set_dataPaste()`, `load()`, `update()`, and custom query methods. Follow the existing model's casing and field naming, even when database fields are uppercase in some places and lowercase in others.

Input helpers such as `getVal()`, `getValInteger()`, `postVal()`, and `postValInteger()` are used throughout controllers. Prefer those local helpers when editing nearby code.

Authentication and access checks are typically done with `User::minAccesslevel(...)` or session data in `$_SESSION['userConnect']`. Match the controller's existing access-control pattern.

## Frontend Notes

The default layout already loads jQuery 1.10, jQuery UI, Fancybox, bxSlider, DataTables CSS, Font Awesome 5.1, and common scripts such as `script.js`, `library.js`, `comment.js`, and for logged-in users `collection.js`.

Before adding new JS or CSS dependencies, check whether the needed behavior is already covered by these assets. For page-specific frontend changes, prefer adding/adjusting files in `public/script/` or `public/style/` and include them through the view when needed.

## Working Guidelines

- Keep changes localized; this codebase has many legacy coupling points.
- Inspect nearby controllers, models, and templates before editing because conventions vary by feature.
- Preserve French user-facing text unless the surrounding page already uses another language.
- Avoid broad refactors while fixing a feature.
- Be careful with dynamic properties: several core classes rely on `#[AllowDynamicProperties]`.
- When creating new pages or actions, create the controller method and the matching `.phtml` template path expected by the router/view system.

