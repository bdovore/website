<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
class Pagination{

/**
* Affiche la pagination à l'endroit où cette fonction est appelée
* @param string $url L'URL ou nom de la page appelant la fonction, ex: 'index.php' ou 'http://example.com/'
* @param string $link La nom du paramètre pour la page affichée dans l'URL, ex: '?page=' ou '?&p='
* @param int $total Le nombre total de pages
* @param int $current Le numéro de la page courante
* @param int $adj (facultatif) Le nombre de pages affichées de chaque côté de la page courante (défaut : 3)
* @return La chaîne de caractères permettant d'afficher la pagination
*/
    public function paginate($url, $link, $total, $current, $adj=3) {
        // Initialisation des variables
        $prev = $current - 1; // numéro de la page précédente
        $next = $current + 1; // numéro de la page suivante
        $penultimate = $total - 1; // numéro de l'avant-dernière page
        $pagination = ''; // variable retour de la fonction : vide tant qu'il n'y a pas au moins 2 pages

        if ($total > 1) {
            // Remplissage de la chaîne de caractères à retourner
            $pagination .= "<div class=\"pagination\">\n";

            /* =================================
             *  Affichage du bouton [précédent]
             * ================================= */
            if ($current == 2) {
                // la page courante est la 2, le bouton renvoie donc sur la page 1, remarquez qu'il est inutile de mettre $url{$link}1
                $pagination .= "<a href=\"{$url}\">◄</a>";
            } elseif ($current > 2) {
                // la page courante est supérieure à 2, le bouton renvoie sur la page dont le numéro est immédiatement inférieur
                $pagination .= "<a href=\"{$url}{$link}{$prev}\">◄</a>";
            } else {
                // dans tous les autres, cas la page est 1 : désactivation du bouton [précédent]
                $pagination .= '<span class="inactive">◄</span>';
            }

            /**
             * Début affichage des pages, l'exemple reprend le cas de 3 numéros de pages adjacents (par défaut) de chaque côté du numéro courant
             * - CAS 1 : il y a au plus 12 pages, insuffisant pour faire une troncature
             * - CAS 2 : il y a au moins 13 pages, on effectue la troncature pour afficher 11 numéros de pages au total
             */

            /* ===============================================
             *  CAS 1 : au plus 12 pages -> pas de troncature
             * =============================================== */
            if ($total < 7 + ($adj * 2)) {
                // Ajout de la page 1 : on la traite en dehors de la boucle pour n'avoir que index.php au lieu de index.php?p=1 et ainsi éviter le duplicate content
                $pagination .= ($current == 1) ? '<span class="active">1</span>' : "<a href=\"{$url}\">1</a>"; // Opérateur ternaire : (condition) ? 'valeur si vrai' : 'valeur si fausse'

                // Pour les pages restantes on utilise itère
                for ($i=2; $i<=$total; $i++) {
                    if ($i == $current) {
                        // Le numéro de la page courante est mis en évidence (cf. CSS)
                        $pagination .= "<span class=\"active\">{$i}</span>";
                    } else {
                        // Les autres sont affichées normalement
                        $pagination .= "<a href=\"{$url}{$link}{$i}\">{$i}</a>";
                    }
                }
            }
            /* =========================================
             *  CAS 2 : au moins 13 pages -> troncature
             * ========================================= */
            else {
                /**
                 * Troncature 1 : on se situe dans la partie proche des premières pages, on tronque donc la fin de la pagination.
                 * l'affichage sera de neuf numéros de pages à gauche ... deux à droite
                 * 1 2 3 4 5 6 7 8 9 … 16 17
                 */
                if ($current < 2 + ($adj * 2)) {
                    // Affichage du numéro de page 1
                    $pagination .= ($current == 1) ? "<span class=\"active\">1</span>" : "<a href=\"{$url}\">1</a>";

                    // puis des huit autres suivants
                    for ($i = 2; $i < 4 + ($adj * 2); $i++) {
                        if ($i == $current) {
                            $pagination .= "<span class=\"active\">{$i}</span>";
                        } else {
                            $pagination .= "<a href=\"{$url}{$link}{$i}\">{$i}</a>";
                        }
                    }

                    // ... pour marquer la troncature
                    $pagination .= '&hellip;';

                    // et enfin les deux derniers numéros
                    $pagination .= "<a href=\"{$url}{$link}{$penultimate}\">{$penultimate}</a>";
                    $pagination .= "<a href=\"{$url}{$link}{$total}\">{$total}</a>";
                }
                /**
                 * Troncature 2 : on se situe dans la partie centrale de notre pagination, on tronque donc le début et la fin de la pagination.
                 * l'affichage sera deux numéros de pages à gauche ... sept au centre ... deux à droite
                 * 1 2 … 5 6 7 8 9 10 11 … 16 17
                 */
                elseif ( (($adj * 2) + 1 < $current) && ($current < $total - ($adj * 2)) ) {
                    // Affichage des numéros 1 et 2
                    $pagination .= "<a href=\"{$url}\">1</a>";
                    $pagination .= "<a href=\"{$url}{$link}2\">2</a>";
                    $pagination .= '&hellip;';

                    // les pages du milieu : les trois précédant la page courante, la page courante, puis les trois lui succédant
                    for ($i = $current - $adj; $i <= $current + $adj; $i++) {
                        if ($i == $current) {
                            $pagination .= "<span class=\"active\">{$i}</span>";
                        } else {
                            $pagination .= "<a href=\"{$url}{$link}{$i}\">{$i}</a>";
                        }
                    }

                    $pagination .= '&hellip;';

                    // et les deux derniers numéros
                    $pagination .= "<a href=\"{$url}{$link}{$penultimate}\">{$penultimate}</a>";
                    $pagination .= "<a href=\"{$url}{$link}{$total}\">{$total}</a>";
                }
                /**
                 * Troncature 3 : on se situe dans la partie de droite, on tronque donc le début de la pagination.
                 * l'affichage sera deux numéros de pages à gauche ... neuf à droite
                 * 1 2 … 9 10 11 12 13 14 15 16 17
                 */
                else {
                    // Affichage des numéros 1 et 2
                    $pagination .= "<a href=\"{$url}\">1</a>";
                    $pagination .= "<a href=\"{$url}{$link}2\">2</a>";
                    $pagination .= '&hellip;';

                    // puis des neuf derniers numéros
                    for ($i = $total - (2 + ($adj * 2)); $i <= $total; $i++) {
                        if ($i == $current) {
                            $pagination .= "<span class=\"active\">{$i}</span>";
                        } else {
                            $pagination .= "<a href=\"{$url}{$link}{$i}\">{$i}</a>";
                        }
                    }
                }
            }

            /* ===============================
             *  Affichage du bouton [suivant]
             * =============================== */
            if ($current == $total)
                $pagination .= "<span class=\"inactive\">►</span>\n";
            else
                $pagination .= "<a href=\"{$url}{$link}{$next}\">►</a>\n";

            // Fermeture de la <div> d'affichage
            $pagination .= "</div>\n";
        }

        return ($pagination);
    }

    public function menuCollection() {


        // tableaux contenant les liens d'accès et le texte à afficher
        $tab_menu_lien = array( "macollection", "macollection/mesetageres", "macollection/messeries", "macollection/mesauteurs", "macollection/futursachats", "macollection/monactu", "macollection/seriecomplete", "macollection/addition", "macollection/proposition","macollection/statistiques");
        $tab_menu_texte = array( "Accueil", "Mes Albums", "Mes Séries", "Mes Auteurs", "Wishlist", "Mon Actu", "A compléter", "L'Addition","Propositions", "Stats" );

        // informations sur la page

        $menu = "\n<div id=\"menu\" data-menu-title=\"Ma Collection\">\n    <ul id=\"onglets\">\n";

        $a_request_uri = explode('?',$_SERVER['REQUEST_URI']);

        $onglet = $a_request_uri[0];

        // boucle qui parcours les deux tableaux
        $i = 0;
        foreach($tab_menu_lien as $cle=>$lien)
        {
            $i = $i + 1;
           /* if ($i == 5) {
                // on ajoute la drop down
                $menu .= " <li class='dropdown-toggle'>Plus ⋮ 
                <ul class='dropdown-menu'>";
            } */
            $menu .= "    <li";


            // si le nom du fichier correspond à celui pointé par l'indice, alors on l'active
            if( $onglet == "/".$lien )
                $menu .= " class=\"active\"";

            $menu .= "><a href=\"" . BDO_URL.$lien . "\">" . $tab_menu_texte[$cle] . "</a></li>\n";
        }

        $menu .= "</ul>\n</div>    <hr class='expSep'>";
        // on ajoute le javascript 
        $menu .= "<script>
            window.addEventListener('load', scrollToActiveTab);
        window.addEventListener('resize', scrollToActiveTab);

        function scrollToActiveTab() {
            const ul = document.getElementById('onglets');
            const activeTab = ul.querySelector('li.active');

            if (activeTab) {
                // Calculer le décalage par rapport au conteneur
                const ulRect = ul.getBoundingClientRect();
                const activeTabRect = activeTab.getBoundingClientRect();

                // Calculer le décalage nécessaire pour centrer l'onglet actif
                const scrollLeft = activeTabRect.left - ulRect.left + ul.scrollLeft - (ulRect.width / 2) + (activeTabRect.width / 2);

                // Faire défiler vers l'onglet actif
                ul.scrollTo({
                    left: scrollLeft,
                    behavior: 'smooth' // Pour un défilement fluide
                });
            }
        }
    </script>";
        // on renvoie le code xHTML
        return $menu;


    }

     public function menuGuest($user_id) {
         // tableaux contenant les liens d'accès et le texte à afficher
        $tab_menu_lien = array( "guest", "guest/collection", "guest/avis", "guest/wishlist" );
        $tab_menu_texte = array( "Accueil","Tous les albums","Avis de lecture","Wish-list");

        $menu = "\n<div id=\"menu\">\n    <ul id=\"onglets\">\n";

        $a_request_uri = explode('?',$_SERVER['REQUEST_URI']);

        $onglet = $a_request_uri[0];

        // boucle qui parcours les deux tableaux
        foreach($tab_menu_lien as $cle=>$lien)
        {
            $menu .= "    <li";


            // si le nom du fichier correspond à celui pointé par l'indice, alors on l'active
            if( $onglet == "/".$lien )
                $menu .= " class=\"active\"";

            $menu .= "><a href=\"" . BDO_URL.$lien . "?user=".$user_id."\">" . $tab_menu_texte[$cle] . "</a></li>\n";
        }

        $menu .= "</ul>\n</div>";

        // on renvoie le code xHTML
        return $menu;
     }

     public function menuAdmin() {


        // tableaux contenant les liens d'accès et le texte à afficher
        $tab_menu_lien = array( "admin", "adminproposition", "adminauteur", "controle", "admin/ajout", "adminnews", "admin/user");
        $tab_menu_texte = array( "Tableau de bord", "Propositions", "Auteurs", "Contrôles", "Ajout",  "News","Utilisateurs" );

        // informations sur la page

        $menu = "\n<div id=\"menu\">\n    <ul id=\"onglets\">\n";

        $a_request_uri = explode('?',$_SERVER['REQUEST_URI']);

        $onglet = $a_request_uri[0];

        // boucle qui parcours les deux tableaux
        foreach($tab_menu_lien as $cle=>$lien)
        {
            $menu .= "    <li";


            // si le nom du fichier correspond à celui pointé par l'indice, alors on l'active
            if( $onglet == "/".$lien )
                $menu .= " class=\"active\"";

            $menu .= "><a href=\"" . BDO_URL.$lien . "\">" . $tab_menu_texte[$cle] . "</a></li>\n";
        }

        $menu .= "</ul>\n</div>";

        // on renvoie le code xHTML
        return $menu;


    }
 }
?>
