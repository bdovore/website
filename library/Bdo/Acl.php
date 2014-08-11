<?php

/**
 * 
 */
class Bdo_Acl
{

    protected $viewAclCreate = false;

    public $error = array();

    public $cache = '';

    protected $_acl = null;

    protected $a_allRole = array();

    protected $a_allResource = array();

    protected $a_allPrivilegeByResource = array();

    protected $a_lineCreationRole = array();

    protected $a_roleByParent = array();

    public $a_accessByRole = array();

    public $a_ctrlAccessByRole = array();

    protected $a_cache = array();

    public function __construct ()
    {
        $this->_acl = new Zend_Acl();
        
        if (BDO_CACHE_ENABLED) {
            
            $this->cache = new Bdo_Cache('acl.serial');
        }
        // $this->reinitCacheFile();
        $this->load();
        $this->init();
    }

    public function reinit ()
    {
        $this->reinitCacheFile();
        
        $this->a_allRole = array();
        $this->a_allResource = array();
        $this->a_allPrivilegeByResource = array();
        $this->a_lineCreationRole = array();
        $this->a_roleByParent = array();
        $this->a_ctrlAccessByRole = array();
        $this->a_accessByRole = array();
        
        unset($this->_acl);
        $this->_acl = new Zend_Acl();
        
        // $this->cache->delete();
        
        $this->load();
        
        $this->init();
    }

    /**
     *
     * @return array
     */
    protected function allRole ()
    {
        $a_entity = $this->a_cache['a_allRole'];
        $a_entity['all'] = 'Tous';
        return $a_entity;
    }

    /**
     *
     * @return array
     */
    protected function allResource ()
    {
        $a_entity = $this->a_cache['a_allResource'];
        $a_entity['all'] = 'Tous';
        return $a_entity;
    }

    /**
     *
     * @return array
     */
    protected function allPrivilegeByResource ()
    {
        $a_entity = $this->a_cache['a_allPrivilegeByResource'];
        
        foreach ($a_entity as $idres => $a_priv) {
            $a_entity[$idres]['all'] = 'Tous';
        }
        $a_entity['all']['all'] = 'Tous';
        
        return $a_entity;
    }

    private function load ()
    {
        $this->a_cache = $this->cache->load();
        
        $this->a_allRole = $this->allRole();
        
        $this->a_allResource = $this->allResource();
        $this->a_allPrivilegeByResource = $this->allPrivilegeByResource();
        
        $this->a_ctrlAccessByRole = $this->a_cache['a_ctrlAccessByRole'];
        $this->a_roleByParent = $this->a_cache['a_roleByParent'];
        
        foreach ($this->a_ctrlAccessByRole as $key => $access) {
            $a_id = explode('-', $key);
            // nettoyage au chargement
            if (isset($this->a_allRole[$a_id[0]]) 
                    and isset($this->a_allResource[$a_id[1]]) 
                    and isset($this->a_allPrivilegeByResource[$a_id[1]][$a_id[2]])){
                $this->a_accessByRole[$a_id[0]][$a_id[1]][$a_id[2]] = $access;
            }
            else {
                unset($this->a_ctrlAccessByRole[$key]);
            }
        }
    }

    public function reinitCacheFile ()
    {
        /*
        $this->a_allRole = array(
                0 => "Aministrateur",
                1 => "Modérateur",
                2 => "Membre",
                5 => "Visiteur",
                98 => "En attente",
                99 => "Désactivé"
        );
        $this->a_allRole = array(
                1 => 'Non connecté',
                2 => 'Connecté',
                3 => 'Correcteur',
                4 => 'Administrateur',
                5 => 'En attente',
                6 => 'Désactivé'
        );
        $this->a_allResource = array(
                0 => 'Index',
                1 => 'Acl',
                2 => 'Profil',
                3 => 'Propal'
        );
        $this->a_allPrivilegeByResource = array(
                0 => array(
                        0 => 'Index'
                ),
                1 => array(
                        0 => 'Index'
                )
        );
        $this->a_ctrlAccessByRole = array(
                '0-all-all' => 0
        );
        */
        unset($this->a_allRole['all']);
        unset($this->a_allResource['all']);
        foreach ($this->a_allPrivilegeByResource as $idres=>$privByRes) {
            if (isset($privByRes['all'])) unset($this->a_allPrivilegeByResource[$idres]['all']);
        }
        
        $this->a_cache = array(
                "a_allRole" => $this->a_allRole,
                "a_allResource" => $this->a_allResource,
                "a_roleByParent" => $this->a_roleByParent,
                "a_allPrivilegeByResource" => $this->a_allPrivilegeByResource,
                "a_ctrlAccessByRole" => $this->a_ctrlAccessByRole
        );
        
        $this->cache->save($this->a_cache);
    }

    public function init ()
    {
        // sauvegarde : $a_roleByParent destine a destruction
        $a_roleByParent = $this->a_roleByParent;
        
        // detection de boucles
        foreach ($a_roleByParent as $id_acl_role_parent => $a_role) {
            $this->legacy($id_acl_role_parent);
        }
        
        // Parcours des chaines
        while (! empty($a_roleByParent)) {
            foreach ($a_roleByParent as $id_acl_role_parent => $a_role) {
                foreach ($a_role as $key => $id_acl_role) {
                    // le role n'est pas/plus un parent
                    if (! isset($a_roleByParent[$id_acl_role])) {
                        // le role n'est pas deja cree
                        if (! isset($this->a_lineCreationRole[$id_acl_role])) {
                            // ajout du role dans la ligne de creation
                            $this->a_lineCreationRole[$id_acl_role] = $id_acl_role;
                        }
                        
                        // suppression du role
                        unset($a_roleByParent[$id_acl_role_parent][$key]);
                    }
                }
                if (empty($a_roleByParent[$id_acl_role_parent])) {
                    // ajout du parent dans la ligne de creation
                    $this->a_lineCreationRole[$id_acl_role_parent] = $id_acl_role_parent;
                    // suppression du parent
                    unset($a_roleByParent[$id_acl_role_parent]);
                }
            }
        }
        
        // realisation ligne de creation totale
        // add independent role
        foreach ($this->a_allRole as $id_acl_role => $role) {
            if (! isset($this->a_lineCreationRole[$id_acl_role])) {
                $this->a_lineCreationRole[$id_acl_role] = $id_acl_role;
            }
        }
        
        // creation resource
        foreach ($this->a_allResource as $id_acl_resource => $nom_acl_resource) {
            if ('all' !== $id_acl_resource) {
                if ($this->viewAclCreate) echo '<br />$this->_acl->add(new Zend_Acl_Resource(' . $nom_acl_resource . '));';
                
                $this->_acl->add(new Zend_Acl_Resource($nom_acl_resource));
                
                if (isset($this->a_allPrivilegeByResource[$id_acl_resource])) {
                    
                    foreach ($this->a_allPrivilegeByResource[$id_acl_resource] as $id_acl_resource_privilege => $nom_acl_resource_privilege) {
                        if ('all' !== $id_acl_resource_privilege) {
                            if ($this->viewAclCreate) echo '<br />$this->_acl->add(new Zend_Acl_Resource(' . $nom_acl_resource . '.' . $nom_acl_resource_privilege . '));';
                            $this->_acl->add(new Zend_Acl_Resource($nom_acl_resource . '.' . $nom_acl_resource_privilege));
                        }
                    }
                }
            }
        }
        
        // parcours ligne creation
        foreach ($this->a_lineCreationRole as $id_acl_role) {
            $roleParent = array();
            if (isset($this->a_roleByParent[$id_acl_role])) {
                foreach ($this->a_roleByParent[$id_acl_role] as $id_acl_role_parent) {
                    $roleParent[] = $id_acl_role_parent;
                }
            }
            
            // creation role
            if ('all' !== $id_acl_role) {
                if ($this->viewAclCreate) echo '<br />$this->_acl->addRole ("' . $id_acl_role . '", ' . (empty($roleParent) ? 'null' : 'array("' . implode('","', $roleParent) . '")') . ');';
                $this->_acl->addRole(new Zend_Acl_Role($id_acl_role), (empty($roleParent) ? null : $roleParent));
            }
            
            // creation privilege by resource
            if (isset($this->a_accessByRole[$id_acl_role])) {
                foreach ($this->a_accessByRole[$id_acl_role] as $id_acl_resource => $a_accessPrivilege) {
                    foreach ($a_accessPrivilege as $id_acl_privilege => $accessDeny) {
                        if ($accessDeny) {
                            if ($this->viewAclCreate) echo '<br />$this->_acl->deny(' . (('all' === $id_acl_role) ? 'null' : '"' . $id_acl_role . '"') . ', ' . (is_null($this->a_allResource[$id_acl_resource]) ? 'null' : '"' . $this->a_allResource[$id_acl_resource] . '"') . ', ' .
                                     (is_null($this->a_allPrivilegeByResource[$id_acl_resource][$id_acl_privilege]) ? 'null' : '"' . $this->a_allPrivilegeByResource[$id_acl_resource][$id_acl_privilege] . '"') . ');';
                            $this->_acl->deny((('all' === $id_acl_role) ? null : $id_acl_role), (('all' === $id_acl_resource) ? null : $this->a_allResource[$id_acl_resource]), (('all' === $id_acl_privilege) ? null : $this->a_allPrivilegeByResource[$id_acl_resource][$id_acl_privilege]));
                        }
                        else {
                            if ($this->viewAclCreate) echo '<br />$this->_acl->allow(' . (('all' === $id_acl_role) ? 'null' : '"' . $id_acl_role . '"') . ', ' . (is_null($this->a_allResource[$id_acl_resource]) ? 'null' : '"' . $this->a_allResource[$id_acl_resource] . '"') . ', ' .
                                     (is_null($this->a_allPrivilegeByResource[$id_acl_resource][$id_acl_privilege]) ? 'null' : '"' . $this->a_allPrivilegeByResource[$id_acl_resource][$id_acl_privilege] . '"') . ');';
                            $this->_acl->allow((('all' === $id_acl_role) ? null : $id_acl_role), (('all' === $id_acl_resource) ? null : $this->a_allResource[$id_acl_resource]), (('all' === $id_acl_privilege) ? null : $this->a_allPrivilegeByResource[$id_acl_resource][$id_acl_privilege]));
                        }
                    }
                }
            }
        }
        
        return true;
    }

    /**
     *
     * @return
     *
     *
     *
     *
     *
     *
     *
     *
     */
    public function changeLegacy ($id_acl_role = null, $id_acl_role_parent = null)
    {
        if ($this->isParent($id_acl_role, $id_acl_role_parent)) {
            foreach ($this->a_roleByParent[$id_acl_role_parent] as $key => $val) {
                if ($val == $id_acl_role) {
                    unset($this->a_roleByParent[$id_acl_role_parent][$key]);
                }
            }
            $this->reinit();
        }
        else if (($id_acl_role == $id_acl_role_parent) or (in_array($id_acl_role_parent, $this->getLegacy($id_acl_role)))) {
            $this->error[] = 'impossibility in class [' . __CLASS__ . '] function[' . __FUNCTION__ . ']';
        }
        else {
            $this->a_roleByParent[$id_acl_role_parent][] = $id_acl_role;
            $this->reinit();
        }
        return $this->error;
    }

    /**
     *
     * @return
     *
     *
     *
     *
     *
     *
     *
     *
     */
    public function changePrivilege ($id_acl_role, $id_acl_resource, $id_acl_privilege)
    {
        $rrp = $id_acl_role . "-" . $id_acl_resource . "-" . $id_acl_privilege;
        
        if ($this->isDefinedPrivilege($id_acl_role, $id_acl_resource, $id_acl_privilege)) {
            if ($this->isAllowedById($id_acl_role, $id_acl_resource, $id_acl_privilege)) {
                unset($this->a_ctrlAccessByRole[$rrp]);
            }
            else {
                $this->a_ctrlAccessByRole[$rrp] = 0;
            }
        }
        else {
            
            if ($this->isAllowedById($id_acl_role, $id_acl_resource, $id_acl_privilege)) {
                $this->a_ctrlAccessByRole[$rrp] = 1;
            }
            else {
                $this->a_ctrlAccessByRole[$rrp] = 0;
            }
        }
        
        $this->reinit();
        
        return $this->error;
    }

    /**
     *
     * @return boolean
     */
    public function accesPriv ($s_resourcePrivilege, $a_idAclRole)
    {
        $usersAllowed = false;
        
        $a_priv = explode('.', $s_resourcePrivilege);
        $resource = $a_priv[0];
        $privilege = $a_priv[1];
        
        // on verifie si l'un des roles du users a les droits d'acces
        foreach ($a_idAclRole as $id_acl_role) {
            if ($usersAllowed = $this->isAllowed($id_acl_role, $resource, $privilege)) {
                break (1);
            }
        }
        
        return $usersAllowed;
    }

    /**
     *
     * @return boolean
     */
    public function isAllowedById ($id_acl_role, $id_acl_resource, $id_acl_privilege)
    {
        $resource = (('all' === $id_acl_resource) ? 'all' : $this->a_allResource[$id_acl_resource]);
        $privilege = (('all' === $id_acl_privilege) ? 'all' : $this->a_allPrivilegeByResource[$id_acl_resource][$id_acl_privilege]);
        return $this->isAllowed($id_acl_role, $resource, $privilege);
    }

    /**
     *
     * @return boolean
     */
    public function isAllowed ($role, $resource, $privilege)
    {
        // $role = ('all' === $role) ? null : strtolower($role);
        // $resource = ('all' === $resource) ? null : strtolower($resource);
        // $privilege = ('all' === $privilege) ? null : strtolower($privilege);
        $role = ('all' === $role) ? null : $role;
        $resource = ('all' === $resource) ? null : $resource;
        $privilege = ('all' === $privilege) ? null : $privilege;
        // echo_pre("$role, $resource, $privilege");
        return $this->_acl->isAllowed($role, $resource, $privilege);
    }

    /**
     *
     * @return boolean
     */
    public function isParent ($id_acl_role, $id_acl_role_parent)
    {
        if (isset($this->a_roleByParent[$id_acl_role_parent])) {
            return in_array($id_acl_role, $this->a_roleByParent[$id_acl_role_parent]);
        }
        else {
            return false;
        }
    }

    /**
     * ligne d'heritage
     * detection de boucle
     *
     * @return array
     */
    protected function legacy ($id_acl_role, $a_descendant = array())
    {
        $a_parent = array();
        
        if (in_array($id_acl_role, $a_descendant)) {
            echo "\ndetection de boucle \najout de " . $id_acl_role . "\n";
            print_r($a_descendant);
            exit();
        }
        else {
            $a_descendant[] = $id_acl_role;
        }
        
        if (isset($this->a_roleByParent[$id_acl_role])) {
            foreach ($this->a_roleByParent[$id_acl_role] as $id_acl_role_parent) {
                $a_parent[] = $id_acl_role_parent;
                $a_parent = array_merge($a_parent, array_diff($this->legacy($id_acl_role_parent, $a_descendant), $a_parent));
            }
        }
        
        return $a_parent;
    }

    /**
     * tout les role dans l'ordre de creation
     *
     * @return array
     */
    public function getAllAclRole ()
    {
        return $this->a_allRole;
    }

    /**
     *
     * @return array
     */
    public function getRole ($id_acl_role)
    {
        return $this->a_allRole[$id_acl_role];
    }

    /**
     *
     * @return array
     */
    public function getResource ($id_acl_resource)
    {
        return $this->a_allResource[$id_acl_resource];
    }

    /**
     *
     * @return array
     */
    public function getResourcePrivilege ($id_acl_resource,$id_acl_resource_privilege)
    {
        return $this->a_allPrivilegeByResource[$id_acl_resource][$id_acl_resource_privilege];
    }

    /**
     * tout les role dans l'ordre de creation
     *
     * @return array
     */
    public function getAllIdRoleByCreationOrder ()
    {
        return $this->a_lineCreationRole;
    }

    /**
     * tout les resource
     *
     * @return array
     */
    public function getAllResource ()
    {
        return $this->a_allResource;
    }

    /**
     * tout les privileges pour une ressource
     *
     * @return array
     */
    public function getAllPrivilegeForResource ($idres)
    {
        $a_priv = array();
        if (isset($this->a_allPrivilegeByResource[$idres])) {
            $a_priv = $this->a_allPrivilegeByResource[$idres];
        }
        return $a_priv;
    }

    /**
     * ligne d'heritage
     *
     * @return array
     */
    public function getLegacy ($id_acl_role)
    {
        return $this->legacy($id_acl_role);
    }

    /**
     *
     * @return array
     */
    public function isLegacyPrivilege ($id_acl_role, $id_acl_resource, $id_acl_privilege)
    {
        foreach ($this->getLegacy($id_acl_role) as $idroleparent) {
            if ($this->isDefinedPrivilege($idroleparent, $id_acl_resource, $id_acl_privilege)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     *
     * @return boolean
     */
    public function isDefinedPrivilege ($id_acl_role, $id_acl_resource, $id_acl_privilege)
    {
        if (isset($this->a_accessByRole[$id_acl_role][$id_acl_resource][$id_acl_privilege])) {
            return true;
        }
        else {
            return false;
        }
    }

    /**
     *
     * @return boolean
     *
     */
    public function addResource ($resource)
    {

        if (! isset($resource['NOM_ACL_RESOURCE']) or empty($resource['NOM_ACL_RESOURCE'])) {
            $this->error[] = "Add resource : the resource name can't be null.";
        }
        else if (in_array($resource['NOM_ACL_RESOURCE'], $this->a_allResource)) {
            $this->error[] = 'Add resource : the resource name  [' . $resource['NOM_ACL_RESOURCE'] . '] already exist.';
        }
        else if (empty($this->error)) {
            $this->a_allResource[] = $resource['NOM_ACL_RESOURCE'];
            $this->reinit();
        }
        return $this->error;
    }
    

    /**
     *
     * @return boolean
     *
     */
    public function updateResource ($resource)
    {

        if (! isset($resource['NOM_ACL_RESOURCE']) or empty($resource['NOM_ACL_RESOURCE'])) {
            $this->error[] = "Add resource : the resource name can't be null.";
        }
        else if (in_array($resource['NOM_ACL_RESOURCE'], $this->a_allResource)) {
            $this->error[] = 'Add resource : the resource name  [' . $resource['NOM_ACL_RESOURCE'] . '] already exist.';
        }
        else if (empty($this->error)) {
            $this->a_allResource['ID_ACL_RESOURCE'] = $resource['NOM_ACL_RESOURCE'];
            $this->reinit();
        }
        return $this->error;
    }

    /**
     *
     * @return boolean
     */
    public function deleteResource ($resource)
    {
        if ($resource['ID_ACL_RESOURCE'] != 'all') {
            unset($this->a_allPrivilegeByResource[$resource['ID_ACL_RESOURCE']]);
            unset($this->a_allResource[$resource['ID_ACL_RESOURCE']]);
            $this->reinit();
        }
        else {
            $this->error[] = 'Delete resource : not remove this element';
        }
        return $this->error;
    }

    /**
     *

     *
     */
    public function addRole ($role)
    {
        // $role->NOM_ACL_ROLE = strtolower($role->NOM_ACL_ROLE);
        if (! isset($role['NOM_ACL_ROLE']) or empty($role['NOM_ACL_ROLE'])) {
            $this->error[] = "Add role : the role name can't be null.";
        }
        else if (in_array($role['NOM_ACL_ROLE'], $this->a_allRole)) {
            $this->error[] = 'Add role : the role name [' . $role['NOM_ACL_ROLE'] . '] already exist.';
        }
        else if (empty($this->error)) {
            $this->a_allRole[] = $role['NOM_ACL_ROLE'];
            $this->reinit();
        }
        return $this->error;
    }
    

    /**
     *

     *        
     */
    public function updateRole ($role)
    {
        // $role->NOM_ACL_ROLE = strtolower($role->NOM_ACL_ROLE);
        if (! isset($role['NOM_ACL_ROLE']) or empty($role['NOM_ACL_ROLE'])) {
            $this->error[] = "Add role : the role name can't be null.";
        }
        else if (in_array($role['NOM_ACL_ROLE'], $this->a_allRole)) {
            $this->error[] = 'Add role : the role name [' . $role['NOM_ACL_ROLE'] . '] already exist.';
        }
        else if (empty($this->error)) {
            $this->a_allRole[$role['ID_ACL_ROLE']] = $role['NOM_ACL_ROLE'];
            $this->reinit();
        }
        return $this->error;
    }

    /**
     *
     * @return boolean
     */
    public function deleteRole ($role)
    {
        if ($role['ID_ACL_ROLE'] != 'all') {
            
            unset($this->a_allRole[$role['ID_ACL_ROLE']]);
            unset($this->a_allRole[$role['ID_ACL_ROLE']]);
            $this->reinit();
        }
        else {
            $this->error[] = 'Delete role : not remove this element';
        }
        return $this->error;
    }

    /**
     *
     * @return boolean
     */
    public function addResourcePrivilege ($resourcePrivilege)
    {
        if (! isset($resourcePrivilege['NOM_ACL_RESOURCE_PRIVILEGE']) or empty($resourcePrivilege['NOM_ACL_RESOURCE_PRIVILEGE'])) {
            $this->error[] = "Add privilege : the privilege name can't be null.";
        }
        if (isset($this->a_allPrivilegeByResource[$resourcePrivilege['ID_ACL_RESOURCE']]) and in_array($resourcePrivilege['NOM_ACL_RESOURCE_PRIVILEGE'], $this->a_allPrivilegeByResource[$resourcePrivilege['ID_ACL_RESOURCE']])) {
            $this->error[] = 'Add privilege : the privilege name [' . $resourcePrivilege['NOM_ACL_RESOURCE_PRIVILEGE'] . '] already exist for this resource.';
        }
        else if (empty($this->error)) {
            $this->a_allPrivilegeByResource[$resourcePrivilege['ID_ACL_RESOURCE']][] = $resourcePrivilege['NOM_ACL_RESOURCE_PRIVILEGE'];
            $this->reinit();
        }
        return $this->error;
    }
    /**
     *
     * @return boolean
     */
    public function updateResourcePrivilege ($resourcePrivilege)
    {
        if (! isset($resourcePrivilege['NOM_ACL_RESOURCE_PRIVILEGE']) or empty($resourcePrivilege['NOM_ACL_RESOURCE_PRIVILEGE'])) {
            $this->error[] = "Add privilege : the privilege name can't be null.";
        }
        if (isset($this->a_allPrivilegeByResource[$resourcePrivilege['ID_ACL_RESOURCE']]) and in_array($resourcePrivilege['NOM_ACL_RESOURCE_PRIVILEGE'], $this->a_allPrivilegeByResource[$resourcePrivilege['ID_ACL_RESOURCE']])) {
            $this->error[] = 'Add privilege : the privilege name [' . $resourcePrivilege['NOM_ACL_RESOURCE_PRIVILEGE'] . '] already exist for this resource.';
        }
        else if (empty($this->error)) {
            $this->a_allPrivilegeByResource[$resourcePrivilege['ID_ACL_RESOURCE']][$resourcePrivilege['ID_ACL_RESOURCE_PRIVILEGE']] = $resourcePrivilege['NOM_ACL_RESOURCE_PRIVILEGE'];
            $this->reinit();
        }
        return $this->error;
    }
    
    /**
     *
     * @return boolean
     */
    public function delResourcePrivilege ($resourcePrivilege)
    {
        unset($this->a_allPrivilegeByResource[$resourcePrivilege['ID_ACL_RESOURCE']][$resourcePrivilege['ID_ACL_RESOURCE_PRIVILEGE']]);
        if(1 == count($this->a_allPrivilegeByResource[$resourcePrivilege['ID_ACL_RESOURCE']])){
            unset($this->a_allPrivilegeByResource[$resourcePrivilege['ID_ACL_RESOURCE']]);
        }
        $this->reinit();
        
        return $this->error;
    }

    /**
     *
     * @return boolean
     */
    public function isResource ($nom_acl_resource)
    {
        // $nom_acl_resource = strtolower($nom_acl_resource);
        if (in_array($nom_acl_resource, $this->a_allResource)) {
            return true;
        }
        
        return false;
    }

    /**
     *
     * @return boolean
     */
    public function isResourcePrivilege ($nom_acl_resource, $nom_acl_resource_privilege)
    {
        foreach ($this->a_allResource as $id_acl_resource => $_nom_acl_resource) {
            if (($nom_acl_resource == $_nom_acl_resource) and (isset($this->a_allPrivilegeByResource[$id_acl_resource])) and (in_array($nom_acl_resource_privilege, $this->a_allPrivilegeByResource[$id_acl_resource]))) {
                return true;
            }
        }
        return false;
    }

    /**
     *
     * @return boolean
     */
    public function getIdPrivilege ($nom_acl_resource, $nom_acl_resource_privilege)
    {
        foreach ($this->a_allResource as $id_acl_resource => $_nom_acl_resource) {
            if (($nom_acl_resource == $_nom_acl_resource) and (in_array($nom_acl_resource_privilege, $this->a_allPrivilegeByResource[$id_acl_resource]))) {
                return true;
            }
        }
        return false;
    }

    public function getDateCreaCache ()
    {
        return $this->cache->dateCreaCache;
    }
}

