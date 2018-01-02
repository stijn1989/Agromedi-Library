<?php
namespace ITC\Auth;

use Phalcon\Acl as PhAcl;
use Phalcon\Acl\Role;
use Phalcon\Acl\Resource;
use Phalcon\Acl\Adapter\Memory as AclList;
use Phalcon\Mvc\Dispatcher;


/**
 * Deze klasse beheerd het ACL gedeelte van een Phalcon MVC applicatie.
 * Het toevoegen van rollen en het toegang verlenen en controleren tot resources gebeurt hier.
 *
 * @package ITC\Auth
 * @author  Stijn Leenknegt <stijn.leenknegt@itconnext.be>
 * @version 1.0
 */
class ACL
{


    /**
     * @var AclList
     */
    private $acl;


    /**
     * Initialiseert de ACL adapter.
     */
    public function __construct()
    {
        $this->acl = new AclList();
        $this->acl->setDefaultAction(PhAcl::DENY);
    }


    /**
     * Voegt een rol toe aan de ACL adapter.
     *
     * @param string $role
     */
    public function addRole($role)
    {
        $this->acl->addrole(new Role($role));
    }


    /**
     * Voegt meerdere rollen toe aan de ACL adapter.
     *
     * @param array $roles
     */
    public function addRoles(array $roles)
    {
        foreach($roles as $role) {
            $this->addRole($role);
        }
    }


    /**
     * Voegt een allowed regel toe aan de ACL adapter.
     * Je kan meerdere rollen dezelfde allowed acties geven.
     * Als de $role parameter gelijk is aan '*', worden alle rollen in de ACL adapter genomen.
     * Als actions één element bevat met de waarde '*', wordt een dispatcher object verwacht.
     * Deze dispatcher object zoekt in de resource class naar de methodes, via reflection, met de naam 'Action' achteraan.
     *
     * @param array|string $role
     * @param string $controller
     * @param array $actions
     * @param Dispatcher|null $d
     */
    public function allow($role, $controller, array $actions, Dispatcher $d = null)
    {
        //if $actions[0] is *, all 'action' methods are allowed!
        if($actions[0] == '*' && $d != null) {
            $actions = $this->getControllerActions($d);
        }

        $this->acl->addResource(new Resource($controller), $actions);
        if(is_array($role)) {
            foreach ($role as $r) {
                foreach ($actions as $a) {
                    $this->acl->allow($r, $controller, $a);
                }
            }
        } elseif($role == '*') {
            $roles = $this->acl->getRoles();
            foreach($roles as $r) {
                foreach($actions as $a) {
                    $this->acl->allow($r->getName(), $controller, $a);
                }
            }
        } else {
            foreach($actions as $a) {
                $this->acl->allow($role, $controller, $a);
            }
        }
    }


    /**
     * Zoekt in de controller klasse naar methodes die eindigen op 'Action'.
     * De methode namen worden terugegeven zonder 'Action'.
     *
     * @param Dispatcher $d
     * @return array
     */
    protected function getControllerActions(Dispatcher $d)
    {
        $actions = [];

        if($d != null) {
            $class = new \ReflectionClass($d->getControllerClass());
            if($class != null) {
                $methods = $class->getMethods();
                foreach($methods as $m) {
                    if(substr($m->getName(), -6) == 'Action') {
                        $actions[] = substr($m->getName(), 0, strpos($m->getName(), 'Action'));
                    }
                }
            }
        }

        return $actions;
    }


    /**
     * Checkt of een rol toegang heeft tot een action van een controller.
     *
     * @param string $role
     * @param string $controller
     * @param string $action
     * @return bool
     */
    public function isAllowed($role, $controller, $action)
    {
        return $this->acl->isAllowed($role, $controller, $action);
    }


}