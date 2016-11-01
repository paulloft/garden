<?php
namespace Addons\Dashboard\Controllers;
use Addons\Dashboard\Models as Model;
use Garden\Exception;
use Garden\Gdn;

/**
* 
*/
class Users extends Base
{
    
    function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $this->permission('dashboard.user.view');

        $this->pageInit();
        $this->title('Users');
        $this->currentUrl('/users');

        $userModel = Model\Users::instance();

        $users = $userModel->getWhere();

        $this->setData('users', $users->as_array());

        $this->render();
    }

    public function groups()
    {
        $this->permission('dashboard.group.view');
        
        $this->pageInit();
        $this->title('User groups');
        $this->currentUrl('/users/groups');

        $groupModel = Model\Groups::instance();

        $groups = $groupModel->getWhere();

        $this->setData('groups', $groups->as_array());
        $this->render();
    }

    public function add()
    {
        $this->edit();
    }

    public function edit($id = false)
    {
        $this->pageInit();
        $this->title($id ? 'Edit user' : 'New user');
        $this->currentUrl('/users');

        $userModel = Model\Users::instance();
        $groupModel = Model\Groups::instance();

        $groups = $groupModel->getWhere(['active'=>1])->as_array();

        if ($id) {
            $this->permission('dashboard.user.edit');
            if (!$user = $userModel->getID($id))
                throw new Exception\NotFound();

            $user['groupsID'] = $user['groupsID'] ? explode(';', $user['groupsID']) : array();
        } else {
            $this->permission('dashboard.user.add');
            $user = array('active' => 1);
        }

        $form = new \Garden\Form();
        $form->setModel($userModel, $user);
        $form->validation()
            ->rule('newpassword', 'min_length', 6)
            ->rule('email', 'email')
            ->rule('email', array($userModel, 'emailAvailable'), $id);


        if ($form->submitted()) {
            $newPass = $form->getValue('newpassword');
            if (!empty($newPass)) {
                $newPass = Model\Auth::instance()->hash($newPass);
                $form->setFormValue('password', $newPass);
            }
            if ($id = $form->save()) {
                $userGroups = val('groupsID', $user);
                $userModel->updateGroups($id, $form->getValues(), $userGroups);
            }
        }

        $this->setData('data', $form->getValues());
        $this->setData('user', $user);
        $this->setData('errors', $form->errors());
        $this->setData('groups', $groups);

        $this->render('user_edit');
    }

    public function groupAdd()
    {
        $this->groupedit();
    }

    public function groupEdit($id = false)
    {
        $this->pageInit();
        $this->title($id ? 'Edit user group' : 'New user group');
        $this->addJs('user_group.js');
        $this->currentUrl('/users/groups');

        $groupModel = Model\Groups::instance();
        $permission = Model\Permission::instance();

        $permList = $permission->getList(true);

        if ($id) {
            $this->permission('dashboard.group.edit');
            if (!$group = $groupModel->getID($id))
                throw new Exception\NotFound();

            $groupPerm = $permission->getForGroup($id);
            $group->permission = array_column($groupPerm, 'id');
        } else {
            $this->permission('dashboard.group.add');
            $group = array('active' => 1);
        }

        $form = new \Garden\Form();
        $form->setModel($groupModel, $group);


        if ($form->submitted()) {
            if ($id = $form->save()) {
                $permission->saveGroup($id, $form->getValues(), $group);
                redirect('/users/groups');
            }
        }

        $this->setData('group', $group);
        $this->setData('permList', $permList);
        $this->setData('data', $form->getValues());
        $this->setData('errors', $form->errors());

        $this->render('group_edit');
    }

    public function forceAuth($id)
    {
        $this->permission('dashboard.user.view');

        $auth = Model\Auth::instance();
        $auth->forceLogin($id);

        redirect('/dashboard');
    }

    public function delete_user($id)
    {
        $this->permission('dashboard.user.delete');

        if ($id == 1) return false;

        $userModel = Model\Users::instance();
        $userModel->delete(array('id' => $id));
    }

    public function delete_group($id)
    {
        $this->permission('dashboard.group.delete');

        if ($id == 1) return false;

        $groupModel = Model\Groups::instance();
        $groupModel->delete(array('id' => $id));
    }

}