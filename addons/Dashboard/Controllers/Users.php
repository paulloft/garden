<?php
namespace Addons\Dashboard\Controllers;

use Addons\Dashboard\Models as Model;
use Garden\Exception;

class Users extends Base {

    public function initialize()
    {
        $this->pageInit();
    }

    public function index()
    {
        $this->title('Users');
        $this->permission('dashboard.user.view');

        $userModel = Model\Users::instance();
        $users = $userModel->getWhere();

        $this->setData('users', $users->as_array());
        $this->render();
    }

    public function groups()
    {
        $this->title('User groups');
        $this->permission('dashboard.group.view');

        $groupModel = Model\Groups::instance();
        $groups = $groupModel->getWhere();

        $this->setData('groups', $groups->as_array());
        $this->render();
    }

    public function add()
    {
        $this->edit(false);
    }

    public function edit($id)
    {
        $this->title($id ? 'Edit user' : 'New user');
        $this->currentUrl('/dashboard/users');

        $userModel = Model\Users::instance();
        $groupModel = Model\Groups::instance();

        $groups = $groupModel->getWhere(['active'=>1])->as_array();

        if ($id) {
            $this->permission('dashboard.user.edit');
            if (!$user = $userModel->getID($id)) {
                throw new Exception\NotFound();
            }

            $user['groupsID'] = $user['groupsID'] ? explode(';', $user['groupsID']) : [];
        } else {
            $this->permission('dashboard.user.add');
            $user = ['active' => 1, 'groupsID' => []];
        }

        $form = $this->form($userModel, $user);

        if ($form->submittedValid()) {
            $newPass = $form->getValue('newpassword');
            if (!empty($newPass)) {
                $form->setFormValue('password', $newPass);
            }

            $id = $form->save();
            if ($id) {
                $userGroups = val('groupsID', $user);
                $userModel->updateGroups($id, $form->getValues(), $userGroups);
                redirect('/dashboard/users');
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
        $this->groupEdit();
    }

    public function groupEdit($id = false)
    {
        $this->title($id ? 'Edit user group' : 'New user group');
        $this->addJs('user_group.js');
        $this->currentUrl('/dashboard/users/groups');

        $groupModel = Model\Groups::instance();
        $permission = Model\Permission::instance();

        $permList = $permission->getList(true);

        if ($id) {
            $this->permission('dashboard.group.edit');
            if (!$group = $groupModel->getID($id)) {
                throw new Exception\NotFound();
            }

            $groupPerm = $permission->getForGroup($id);
            $group['permission'] = array_column($groupPerm, 'id');
        } else {
            $this->permission('dashboard.group.add');
            $group = ['active' => 1];
        }

        $form = $this->form($groupModel, $group);

        if ($form->submitted()) {
            $id = $form->save();
            if ($id) {
                $permission->saveGroup($id, $form->getValues(), $group);
                redirect('/dashboard/users/groups');
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

    public function deleteUser($id)
    {
        $this->permission('dashboard.user.delete');

        if ($id == 1) {
            return false;
        }

        $userModel = Model\Users::instance();
        $userModel->deleteID($id);

        redirect('/dashboard/users');
    }

    public function deleteGroup($id)
    {
        $this->permission('dashboard.group.delete');

        if ($id == 1) {
            return false;
        }

        $groupModel = Model\Groups::instance();
        $groupModel->deleteID($id);

        redirect('/dashboard/users/groups');
    }

}