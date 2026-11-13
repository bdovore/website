<?php

/** Rubrique du header à partir du contrôleur déjà résolu par le routeur MVC. */
function bdoNavigationSection($controller)
{
    $controller = strtolower((string) $controller);
    if (strpos($controller, 'admin') === 0 || in_array($controller, array('acl', 'cache', 'controle'), true)) return 'admin';

    $groups = array(
        'browser' => array('browser', 'album', 'albumcomment', 'auteurbd', 'seriebd', 'collectionbd',
            'liste_auteur_bd', 'liste_serie_bd', 'search', 'discovery', 'simil'),
        'leguide' => array('leguide'),
        'parabd' => array('parabd'),
        'collection' => array('macollection', 'bdtheque', 'guest', 'compte', 'import', 'export', 'auteurfavoris', 'paramcarre', 'proposition'),
        'presentation' => array('presentation', 'contacts', 'tutoriel', 'mentionslegales', 'sponsors'),
        'forum' => array('forum'),
    );
    foreach ($groups as $section => $controllers) {
        if (in_array($controller, $controllers, true)) return $section;
    }
    return 'accueil';
}

/** Un seul lien représente la rubrique courante, sans dépendre de JavaScript. */
function bdoNavigationCurrent($section, $controller)
{
    return bdoNavigationSection($controller) === $section ? ' aria-current="true"' : '';
}
