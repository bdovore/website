<?php
/** Contrat des rubriques du header, sans base ni requête externe. */
require_once dirname(__DIR__) . '/inc/navigation.php';
$checks = 0; $failures = 0;
function navigationAssert($condition, $message) {
    global $checks, $failures; $checks++;
    if (!$condition) { $failures++; fwrite(STDERR, "ECHEC : $message\n"); }
}
foreach (array('Album', 'Auteurbd', 'Seriebd', 'Collectionbd', 'Browser', 'Liste_auteur_bd', 'Liste_serie_bd') as $controller) {
    navigationAssert(bdoNavigationSection($controller) === 'browser', "$controller appartient à Browser");
}
navigationAssert(bdoNavigationSection('LeGuIdE') === 'leguide', 'toutes les actions du Guide utilisent le contrôleur normalisé');
foreach (array('Admin', 'Adminproposition', 'Adminauteur', 'Adminparabd', 'Adminnews') as $controller) {
    navigationAssert(bdoNavigationSection($controller) === 'admin', "$controller appartient à Admin");
}
navigationAssert(bdoNavigationSection('Macollection') === 'collection' && bdoNavigationSection('Auteurfavoris') === 'collection', 'les auteurs favoris restent dans la collection personnelle');
navigationAssert(bdoNavigationSection('Compte') === 'collection', 'le profil avec header appartient à la collection');
navigationAssert(bdoNavigationSection('Parabd') === 'parabd', 'Para-BD garde sa rubrique');
navigationAssert(bdoNavigationSection('Tutoriel') === 'presentation', 'les pages de présentation gardent un contexte');
navigationAssert(bdoNavigationSection('Accueil') === 'accueil' && bdoNavigationSection('Index') === 'accueil', 'accueil et ancienne entrée Index');
foreach (array('Album', 'Leguide', 'Adminproposition', 'Compte', 'Parabd', 'Accueil') as $controller) {
    $selected = array_filter(array('accueil', 'browser', 'leguide', 'parabd', 'collection', 'presentation', 'forum', 'admin'), function ($section) use ($controller) {
        return bdoNavigationCurrent($section, $controller) !== '';
    });
    navigationAssert(count($selected) === 1, "une seule rubrique courante pour $controller");
}
navigationAssert(bdoNavigationCurrent('browser', 'Seriebd') === ' aria-current="true"', 'indication accessible produite côté serveur');
navigationAssert(bdoNavigationCurrent('admin', 'Album') === '', 'aucun rattachement Admin par simple lien ou nom de sous-page');
echo "$checks contrôles de navigation, $failures échec(s).\n"; exit($failures ? 1 : 0);
